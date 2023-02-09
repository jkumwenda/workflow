<?php

namespace App\Services;

use App\Flow;
use App\Unit;
use App\User;
use App\Trail;


use App\Cancel;
use App\Travel;
use \Notification;
use App\Traveller;
use Carbon\Carbon;
use App\Delegation;

use App\Subsistence;


use App\Facades\Requisition;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


use App\Mail\Subsistence\SubsistenceToOwnerMail;
use App\Notifications\Subsistence\SubsistenceApprovalNotification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\Travel\TravelCancelNotification;
use App\Notifications\Subsistence\SubsistenceSendNextNotification;



class SubsistenceService
{
    public function __construct()
    {
    }


    /**
     * Send Next
     *
     * @param Request $request
     * @param Travel $travel
     * @param bool $submit true='submit', false='return'
     */
    public function sendNext($request, Subsistence $subsistence, bool $submit)
    {
        $next = $this->getNextUsers($subsistence, ($submit ? 'next' : 'previous'));
        if (empty($next['users'])) {
            $next['user'] = null;
        } else {
            // Next user should be selected
            if ($request->has('next_user_id') && in_array($request->next_user_id, $next['users']->pluck('id')->all()) !== false) {
                $next['user'] = $next['users']->find($request->next_user_id);
            } else if (count($next['users']) == 1) {

                $next['user'] = User::findOrFail($request->next_user_id);
            } else {
                throw new \Exception('Next user is not requested. Check request');
            }
        }


        // update trail
        $currentTrail = $subsistence->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => $next['flow']->requisition_status_id == config('const.REQUISITION_STATUS.SUBSISTENCE.CLOSED') ? 'NORMAL' : 'CHECKING',
        ]);
        $subsistence->trails()->save($nextTrail);

        // update travels
        $subsistence->requisition_status_id = $next['flow']->requisition_status_id;
        $subsistence->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        $subsistence->save();

        //Delete notification (subsistence)
        //dd($request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'subsistence'")->whereRaw("JSON_VALUE(data, '$.id') = $subsistence->id")->get());
        $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'subsistence'")->whereRaw("JSON_VALUE(data, '$.id') = $subsistence->id")->get();
        if ($notifications) {
            $notifications->markAsRead();
        }


        //notification & send mails to next user
        if (!empty($next['user'])) {
            if ($submit) {
                Notification::send($next['user'], new SubsistenceSendNextNotification($submit ? 'submit' : 'return', $subsistence, $next['flow'], $next['user'], $currentTrail));
                // Mail::to($next['user']->email)->send(new TravelSendNextNotification('submit', $subsistence, $next['flow'], $next['user'], $currentTrail));
            } else {
                if ($next['user']->id != $subsistence->created_user_id) {
                    Notification::send($next['user'], new SubsistenceSendNextNotification('return', $subsistence, $next['flow'], $next['user'], $currentTrail));
                    // Mail::to($next['user']->email)->send(new TravelSendNextNotification('return', $subsistence, $next['flow'], $next['user'], $currentTrail));
                }
            }

            //to owner
            if (Auth::user()->id != $subsistence->travel->procurement->created_user_id) {
                Mail::to($subsistence->travel->procurement->createdUser->email)->send(new SubsistenceToOwnerMail($submit ? 'submit' : 'return', $subsistence, $next['flow'], $next['user'], $currentTrail, null));
            }
        }

        return $next;
    }


    /**
     * Create subsistence requisition
     *
     * @param TravelRequisitionRequest $request
     * @return Travel $subsistence saved data
     */
    public function create($request, $id)
    {
        
        $moduleId = config('const.MODULE.TRAVEL.SUBSISTENCE');
        $requisitionStatusId = config('const.REQUISITION_STATUS.SUBSISTENCE.SUBSISTENCE_GENERATION');
        
        $travel = Travel::findOrFail($id);
        $subsistence = null;

        DB::beginTransaction();
        try {

            //purchase
            $subsistence = new Subsistence([
                'travel_id' => $id,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => $travel->created_user_id,
                'current_user_id' => Auth::user()->id,
            ]);
            $subsistence->save();
            
            foreach($travel->travellers as $traveller){
                $traveller->subsistence_id = $subsistence->id;
                $traveller->save();
            }


            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
            ->join('flow_details', function($join) {
                $join->on('flow_details.flow_id', '=', 'flows.id')->where('level', 1);
            })
            ->where([
                ['module_id', $moduleId],
                ['company_id', Unit::find($travel->procurement->unit_id)->company_id]
            ])->first();

            // create new trail
            // (*) when finish delegating, change user_id if it's delegated. See ProcurementService::finishDelegate
            $trail = new Trail([
                'flow_id' => $flow->id,
                'flow_detail_id' => $flow->flow_detail_id,
                'user_id' => Auth::user()->id,
                'status' => 'CHECKING',
            ]);
            
            $subsistence->trails()->save($trail);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        $next = $this->getNextUsers($subsistence, (true ? 'next' : 'previous'));
        $request->request->add(['next_user_id' => $next['users'][0]->id]);
        $this->sendNext($request, $subsistence, true);

        return $subsistence;
    }

    public function createDriverSubsistence($request, $id)
    {
        $moduleId = config('const.MODULE.TRAVEL.SUBSISTENCE');
        $requisitionStatusId = config('const.REQUISITION_STATUS.SUBSISTENCE.SUBSISTENCE_GENERATION');
        
        $traveller = Traveller::findOrFail($id);
        $subsistence = null;

        DB::beginTransaction();
        try {

            //purchase
            $subsistence = new Subsistence([
                'travel_id' => $traveller->travel_id,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
            ]);
            $subsistence->save();

            $traveller->subsistence_id = $subsistence->id;
            $traveller->save();
           


            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
            ->join('flow_details', function($join) {
                $join->on('flow_details.flow_id', '=', 'flows.id')->where('level', 1);
            })
            ->where([
                ['module_id', $moduleId],
                ['company_id', Unit::find($traveller->travel->procurement->unit_id)->company_id]
            ])->first();

            // create new trail
            // (*) when finish delegating, change user_id if it's delegated. See ProcurementService::finishDelegate
            $trail = new Trail([
                'flow_id' => $flow->id,
                'flow_detail_id' => $flow->flow_detail_id,
                'user_id' => Auth::user()->id,
                'status' => 'CHECKING',
            ]);
            $subsistence->trails()->save($trail);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        $next = $this->getNextUsers($subsistence, (true ? 'next' : 'previous'));
        $request->request->add(['next_user_id' => $next['users'][0]->id]);
        $this->sendNext($request, $subsistence, true);

        return $subsistence;
    }

    /**
     * get next/previous selectable trail users
     *
     * @param Travel $subsistence
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($subsistence, $sign = 'next')
    {
        $currentFlow = $subsistence->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;

        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $subsistence->travel->procurement->unit, $subsistence->createdUser);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $subsistence->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
            if (!empty($previousTrail)) {
                $next['user'] = User::find($previousTrail->user_id);
            }
        }

        return $next;
    }

    /**
     * Submit
     *
     * @param Request $request
     * @param int $id
     * @param boolean submit type  true = submit, false = return
     */
    public function submit($request, $id, $submit = true)
    {
        $subsistence = Subsistence::findOrFail($id);
        if ($subsistence->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $subsistence, $submit);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $subsistence;
    }
    
    public function cancel($request, $id)
    {
        $subsistence = Subsistence::findOrFail($id);
        if (((in_array($subsistence->travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== true)
            || ($subsistence->requisition_status_id == config('const.REQUISITION_STATUS.SUBSISTENCE.CANCELED')))&& !in_array($subsistence->travel->transport->requisition_status_id,
            [
                config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_APPROVAL')
            ])
        ) {
            abort(403, 'Unauthorized action.');
        }


        $currentUserIds = [];
        if (!empty($subsistence->current_user_id)) {
            $currentUserIds[] = $subsistence->current_user_id;
        }

        DB::beginTransaction();
        try {
            // insert cancel table
            $newCancel = new Cancel([
                'comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $subsistence->canceled()->save($newCancel);

            //change requisition status & delete current user id
            $subsistence->requisition_status_id = config('const.REQUISITION_STATUS.SUBSISTENCE.CANCELED');
            $subsistence->current_user_id = null;
            $subsistence->save();

            //Delete current trail
            $subsistence->trails()->where('status', 'CHECKING')->delete();

           
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to current user
        if (!empty($currentUserIds)) {
            $currentUserIds = array_unique($currentUserIds);
            foreach ($currentUserIds as $currentUserId) {
                $currentUser = User::find($currentUserId);
                Notification::send($currentUser, new TravelCancelNotification($subsistence->travel, Auth::user(), $currentUser, $request->comment));
            }
        }

        //to owner
        if ($subsistence->created_user_id != Auth::user()->id) {
            Mail::to($subsistence->createdUser->email)->send(new SubsistenceToOwnerMail('cancel', $subsistence, null, Auth::user(), null, $request->comment));
        }

        DB::commit();

        //delete notifications for all users (both of subsistence)
        // Db::table->delete doesn't delete in the DB transaction...
        //DB::table('notifications')->where([['data->type', 'subsistence'], ['data->id', $subsistence->id]])->delete();
        


        return $subsistence;
    }

    public function approve($request, $id, $submit = true)
    {
        
        $subsistence = Subsistence::findOrFail($id);
        $subsistenceCheck = 0;
        foreach($subsistence->travel->subsistence as $sub){
            if (
                in_array(
                    $sub->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.SUBSISTENCE.CLOSED')
                    ]
                )
            ) {
                $subsistenceCheck++;
            }
        }                       
        if ($subsistence->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $subsistence, $submit);
            Notification::send($subsistence->createdUser, new SubsistenceApprovalNotification($subsistence, Auth::user(), $subsistence->createdUser, $request->comment));
            
            if (
                in_array(
                    $subsistence->travel->transport->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.TRANSPORT.CLOSED')
                    ]
                ) && $subsistenceCheck >= 1
            ) {
                $travelService = new TravelService();
                $travel = $travelService->sendNext($request, $subsistence->travel, true);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();


        return $subsistence;
    }

}

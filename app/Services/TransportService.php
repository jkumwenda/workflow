<?php

namespace App\Services;

use App\Flow;
use App\Item;
use App\Unit;
use App\User;
use App\Trail;

use App\Cancel;
use App\Travel;
use App\Document;
use \Notification;
use App\Transport;
use App\Traveller;
use Carbon\Carbon;


use App\Subsistence;

use App\Facades\Requisition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\Transport\TransportToOwnerMail;
use App\Notifications\Travel\TravelCancelNotification;
use App\Notifications\Transport\TransportApprovalNotification;
use App\Notifications\Transport\TransportSendNextNotification;

class TransportService
{
    public function __construct()
    {
    }


    /**
     * Send Next
     *
     * @param Request $request
     * @param Transport $travel
     * @param bool $submit true='submit', false='return'
     */
    public function sendNext($request, Transport $transport, bool $submit)
    {
        $next = $this->getNextUsers($transport, ($submit ? 'next' : 'previous'));
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
        $currentTrail = $transport->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => $next['flow']->requisition_status_id == config('const.REQUISITION_STATUS.TRANSPORT.CLOSED') ? 'NORMAL' : 'CHECKING',
        ]);
        $transport->trails()->save($nextTrail);

        // update travels
        $transport->requisition_status_id = $next['flow']->requisition_status_id;
        $transport->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        $transport->save();

        //Delete notification (travel)
        //dd($request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'travel'")->whereRaw("JSON_VALUE(data, '$.id') = $travel->id")->get());
        $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'transport'")->whereRaw("JSON_VALUE(data, '$.id') = $transport->id")->get();
        if ($notifications) {
            $notifications->markAsRead();
        }


        //notification & send mails
        //to next user
        if (!empty($next['user'])) {
            if ($submit) {
                Notification::send($next['user'], new TransportSendNextNotification($submit ? 'submit' : 'return', $transport, $next['flow'], $next['user'], $currentTrail));
                // Mail::to($next['user']->email)->send(new TravelSendNextNotification('submit', $travel, $next['flow'], $next['user'], $currentTrail));
            } else {
                if ($next['user']->id != $transport->created_user_id) {
                    Notification::send($next['user'], new TransportSendNextNotification('return', $transport, $next['flow'], $next['user'], $currentTrail));
                    // Mail::to($next['user']->email)->send(new TravelSendNextNotification('return', $travel, $next['flow'], $next['user'], $currentTrail));
                }
            }

            //to owner
            if (Auth::user()->id != $transport->travel->procurement->created_user_id) {
                Mail::to($transport->travel->procurement->createdUser->email)->send(new TransportToOwnerMail($submit ? 'submit' : 'return', $transport, $next['flow'], $next['user'], $currentTrail, null));
            }
        }

        return $next;
    }


    public function create($request, $id)
    {
        $moduleId = config('const.MODULE.TRAVEL.TRANSPORT');
        $requisitionStatusId = config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_GENERATION');
        $travel = Travel::findOrFail($id);
        $transport = null;

        DB::beginTransaction();
        try {

            //purchase
            $transport = new Transport([
                'travel_id' => $id,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => $travel->created_user_id,
                'current_user_id' => Auth::user()->id,
            ]);
            $transport->save();


            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
                ->join('flow_details', function ($join) {
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
            $transport->trails()->save($trail);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        $next = $this->getNextUsers($transport, (true ? 'next' : 'previous'));
        $request->request->add(['next_user_id' => $next['users'][0]->id]);
        $this->sendNext($request, $transport, true);

        return $transport;
    }

    public function allocate($request, $id)
    {
        $transport = Transport::findOrFail($id);
        $travel = $transport->travel;

        $saveData = [];


        DB::beginTransaction();
        try {

            if (!$travel->driver_id) {
                $travel->vehicle_id = $request->vehicle;
                $travel->driver_id = $request->driver;
                $travel->update();

                $driverId = $travel->driver_id;
                $driver = User::findOrFail($driverId);
                if ($travel->campus->district_id != $travel->destination) {
                    $amount = $driver->grade->subsistence;
                } else {
                    $amount = $driver->grade->lunch;
                }

                $saveData = [
                    'travel_id' => $travel->id,
                    'user_id' => $driverId,
                    'departure_date' => $travel->datetime_out,
                    'return_date' => $travel->datetime_in,
                    'amount' => $amount,
                    'accomodation_provided' => 'yes',
                    'created_user_id' => Auth::user()->id,
                    'current_user_id' => Auth::user()->id,
                ];
                $addDriver = new Traveller($saveData);
                $addDriver->save();

                $subsistenceService = new SubsistenceService();
                $subsistence = $subsistenceService->createDriverSubsistence($request, $addDriver->id);
            } else {
                // modify
                $driver = Traveller::where('travel_id', $travel->id)->where('user_id', $travel->driver_id)->first();
                $driverAmount = User::findOrFail($request->driver);
                if ($travel->campus->district_id != $travel->destination) {
                    $amount = $driverAmount->grade->subsistence;
                } else {
                    $amount = $driverAmount->grade->lunch;
                }

                $saveData = [
                    'user_id' => (int)$request->driver,
                    'amount' => $amount,
                    'accomodation_provided' => 'yes',
                ];

                $driver->update($saveData);

                $travel->vehicle_id = $request->vehicle;
                $travel->driver_id = $request->driver;
                $travel->update();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $transport;
    }

    /**
     * get next/previous selectable trail users
     *
     * @param Transport $travel
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($transport, $sign = 'next')
    {
        $currentFlow = $transport->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;
        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $transport->travel->procurement->unit, $transport->createdUser);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $transport->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
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
        $transport = Transport::findOrFail($id);
        if ($transport->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $transport, $submit);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $transport;
    }

    /**
     * Cancel
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */
    public function cancel($request, $id)
    {
        $transport = Transport::findOrFail($id);

        if (((in_array($transport->travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== true)
                || ($transport->requisition_status_id == config('const.REQUISITION_STATUS.TRANSPORT.CANCELED'))) &&
            !in_array(
                $transport->requisition_status_id,
                [
                    config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_APPROVAL')
                ]
            )
        ) {
            abort(403, 'Unauthorized action.');
        }


        $currentUserIds = [];
        if (!empty($transport->current_user_id)) {
            $currentUserIds[] = $transport->current_user_id;
        }

        DB::beginTransaction();
        try {
            // insert cancel table
            $newCancel = new Cancel([
                'comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $transport->canceled()->save($newCancel);

            //change requisition status & delete current user id
            $transport->requisition_status_id = config('const.REQUISITION_STATUS.TRANSPORT.CANCELED');
            $transport->current_user_id = null;
            $transport->save();

            //Delete current trail
            $transport->trails()->where('status', 'CHECKING')->delete();
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
                Notification::send($currentUser, new TravelCancelNotification($transport->travel, Auth::user(), $currentUser, $request->comment));
            }
        }

        //to owner
        if ($transport->created_user_id != Auth::user()->id) {
            Mail::to($transport->createdUser->email)->send(new TransportToOwnerMail('cancel', $transport, null, Auth::user(), null, $request->comment));
        }

        DB::commit();

        //delete notifications for all users (both of transport)
        // Db::table->delete doesn't delete in the DB transaction...
        //DB::table('notifications')->where([['data->type', 'transport'], ['data->id', $transport->id]])->delete();



        return $transport;
    }

    /**
     * Change owner
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */

    public function approve($request, $id, $submit = true)
    {
        $transport = Transport::findOrFail($id);

        $subsistenceCheck = 0;
        foreach ($transport->travel->subsistence as $sub) {
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
        
        if ($transport->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $transport, $submit);
            Notification::send($transport->createdUser, new TransportApprovalNotification($transport, Auth::user(), $transport->createdUser, $request->comment));
            //Mail::to($transport->createdUser->email)->send(new TransportToOwnerMail('approve', $transport, null, Auth::user(), null, $request->comment));

            if ($subsistenceCheck > 1) {

                $travelService = new TravelService();
                $travel = $travelService->sendNext($request, $transport->travel, true);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();


        return $transport;
    }
}

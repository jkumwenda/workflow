<?php

namespace App\Services;

use App\Campus;
use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Travel;
use App\Traveller;
use App\Flow;
use App\User;
use App\Unit;
use App\Trail;
use App\Document;
use App\Delegation;
use App\Cancel;
use App\ChangeLog;

use App\Facades\Requisition;

use App\Mail\Travel\TravelToOwnerMail;
use App\Notifications\Travel\TravelSendNextNotification;
use App\Notifications\Travel\TravelDelegateNotification;
use App\Notifications\Travel\TravelFinishDelegateNotification;
use App\Notifications\Travel\TravelCancelNotification;
use App\Procurement;
use App\Vehicle;
use Carbon\Carbon;

class TravelService
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
    public function sendNext($request, Travel $travel, bool $submit)
    {
        $next = $this->getNextUsers($travel, ($submit ? 'next' : 'previous'));

        if (empty($next['users'])) {
            $next['user'] = null;
        } else {
            // Next user should be selected
            if (empty($request->next_user_id) || in_array($request->next_user_id, $next['users']->pluck('id')->all()) == false) {
                throw new \Exception('Next user is not requested. Check request');
            }
            $next['user'] = $next['users']->find($request->next_user_id);
        }


        // update trail
        $currentTrail = $travel->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => $next['flow']->requisition_status_id == config('const.REQUISITION_STATUS.TRAVEL.CLOSED') ? 'NORMAL' : 'CHECKING',
        ]);
        $travel->trails()->save($nextTrail);

        // update travels
        $travel->requisition_status_id = $next['flow']->requisition_status_id;
        $travel->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        $travel->save();

        //Delete notification (travel)
        //dd($request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'travel'")->whereRaw("JSON_VALUE(data, '$.id') = $travel->id")->get());
        $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'travel'")->whereRaw("JSON_VALUE(data, '$.id') = $travel->id")->get();
        if ($notifications) {
            $notifications->markAsRead();
        }


        //notification & send mails
        //to next user
        if (!empty($next['user'])) {
            if ($submit) {
                Notification::send($next['user'], new TravelSendNextNotification($submit ? 'submit' : 'return', $travel, $next['flow'], $next['user'], $currentTrail));
                // Mail::to($next['user']->email)->send(new TravelSendNextNotification('submit', $travel, $next['flow'], $next['user'], $currentTrail));
            } else {
                if ($next['user']->id != $travel->created_user_id) {
                    Notification::send($next['user'], new TravelSendNextNotification('return', $travel, $next['flow'], $next['user'], $currentTrail));
                    // Mail::to($next['user']->email)->send(new TravelSendNextNotification('return', $travel, $next['flow'], $next['user'], $currentTrail));
                }
            }

            //to owner
            if (Auth::user()->id != $travel->procurement->created_user_id) {
                Mail::to($travel->procurement->createdUser->email)->send(new TravelToOwnerMail($submit ? 'submit' : 'return', $travel, $next['flow'], $next['user'], $currentTrail, null));
            }
        }

        return $next;
    }


    /**
     * Create travel requisition
     *
     * @param TravelRequisitionRequest $request
     * @return Travel $travel saved data
     */
    public function create($request)
    {

        $moduleId = config('const.MODULE.TRAVEL.TRAVEL');
        $requisitionStatusId = config('const.REQUISITION_STATUS.TRAVEL.TRAVEL_PREPARATION');
        $travel = null;
        if ($request->vehicle) {
            $request->request->add(['vehicleType' => Vehicle::findOrFail($request->vehicle)->vehicle_type_id]);
        }


        DB::beginTransaction();
        try {
            //travels

            $requisition = new Procurement([
                'title' => $request->title,
                'unit_id' => $request->unit_id,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,




            ]);
            $requisition->save();

            //Insert Travel details
            $travel = new Travel([
                'procurement_id' => $requisition->id,
                'requisition_status_id' => $requisition->requisition_status_id,
                'purpose' => $request->purpose,
                'vehicle_id' => $request->vehicle,
                'vehicle_type_id' => $request->vehicleType,
                'datetime_out' => $request->departureDate,
                'datetime_in' => $request->returnDate,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
            ]);

            $travel->save();

            // Insert all travellers
            $users = $request->get('users');
            $origin = Campus::findOrFail($request->origin);
            $departureDate = $request->get('userDepartureDate');
            $returnDate = $request->get('userReturnDate');
            $accommodationProvided = $request->get('accommodationProvided');
            $saveData = [];
            foreach ($users as $i => $user) {
                $traveller = User::findOrFail($user);
                if (!$traveller->grade) {
                    $travel->procurement->delete();
                    $travel->delete();
                    $travel = ['id'=>null, 'traveller' => $traveller->first_name.' '.$traveller->surname];
                    return $travel;
                    break;
                } else {
                    if ($travel->campus->district_id != $travel->destination) {
                        $amount = $traveller->grade->subsistence;
                    } else {
                        $amount = $traveller->grade->lunch;
                    }

                    $saveData[] = [
                        'travel_id' => $requisition->travel->id,
                        'user_id' => trim($user),
                        'departure_date' => $departureDate[$i],
                        'return_date' => $returnDate[$i],
                        'amount' => $amount,
                        'accomodation_provided' => $accommodationProvided[$i],
                        'created_user_id' => Auth::user()->id,
                        'current_user_id' => Auth::user()->id,
                    ];
                }
            }
            $requisition->traveller()->insert($saveData);

            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
                ->join('flow_details', function ($join) {
                    $join->on('flow_details.flow_id', '=', 'flows.id')->where('level', 1);
                })
                ->where([
                    ['module_id', $moduleId],
                    ['company_id', Unit::find($request->unit_id)->company_id]
                ])->first();

            // create new trail
            $trail = new Trail([
                'flow_id' => $flow->id,
                'flow_detail_id' => $flow->flow_detail_id,
                'user_id' => Auth::user()->id,
                'status' => 'CHECKING',
            ]);
            $travel->trails()->save($trail);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $travel;
    }


    /**
     * Update travel requisition
     *
     * @param TravelRequisitionRequest $request
     * @return Travel $travel updated data
     */
    public function amend($request, $id)
    {
        $travel = Travel::findOrFail($id);
        $requisition = $travel->procurement;
        $travellers = Traveller::where('travel_id', $travel->id)->get();
        $currentTravellers = $travellers->pluck('id')->all();
        if ($request->vehicle) {
            $request->request->add(['vehicleType' => Vehicle::findOrFail($request->vehicle)->vehicle_type_id]);
        }

        // Check need saving change-logs
        $saveChangeLogs = false;
        if (in_array($requisition->unit_id, Auth::user()->units()->pluck('id')->all()) === false) {
            //if other unit user amend, save change logs
            $saveChangeLogs = true;
        }

        DB::beginTransaction();
        try {

            //requsition details
            $requisition->title = $request->title;
            $requisition->unit_id = $request->unit_id;
            $requisition->save();

            // Travel Details
            $travel->purpose = $request->get('purpose');
            $travel->vehicle_type_id = $request->get('vehicleType');
            $travel->datetime_out = $request->get('departureDate');
            $travel->datetime_in = $request->get('returnDate');
            $travel->origin = $request->get('origin');
            $travel->destination = $request->get('destination');
            $travel->current_user_id = Auth::user()->id;
            $travel->save();

            $saveData = [];
            $users = $request->get('users');
            $departureDate = $request->get('userDepartureDate');
            $returnDate = $request->get('userReturnDate');
            $accommodationProvided = $request->get('accommodationProvided');
            $requisitionTravellerIds = $request->get('requisition_traveller_id');
            foreach ($users as $i => $user) {
                $traveller = User::findOrFail($user);
                if (!$traveller->grade) {
                    $travel->procurement->delete();
                    $travel->delete();
                    $travel = ['id'=>null, 'traveller' => $traveller->first_name.' '.$traveller->surname];
                    return $travel;
                    break;
                } else {
                    if ($travel->campus->district_id != $travel->destination) {
                        $amount = $traveller->grade->subsistence;
                    } else {
                        $amount = $traveller->grade->lunch;
                    }
    
                    $saveData = [
                        'travel_id' => $requisition->travel->id,
                        'user_id' => trim($user),
                        'departure_date' => $departureDate[$i],
                        'return_date' => $returnDate[$i],
                        'amount' => $amount,
                        'accomodation_provided' => $accommodationProvided[$i],
                        'current_user_id' => Auth::user()->id,
                    ];
                    if (is_null($requisitionTravellerIds[$i])) {
                        // add
                        $saveData['created_user_id'] = Auth::user()->id;
                        $addTraveller = new Traveller($saveData);
                        $addTraveller->save();
    
                        if ($saveChangeLogs === true) {
                            $changeLog = new ChangeLog([
                                'crud' => 'Create',
                                'information' => $addData->getAttributes(),
                                'user_id' => Auth::user()->id,
                            ]);
                            $requisition->changeLogs()->save($changeLog);
                        }
                    } else {
                        // modify
                        $modData = Traveller::where('id', $requisitionTravellerIds[$i])->first();
                        $modData->fill($saveData);
                        if ($modData->isDirty()) {
                            if ($saveChangeLogs === true) {
                                $changes = [];
                                foreach ($modData->getDirty() as $k => $v) {
                                    //put original value
                                    $changes[$k] = $modData->getOriginal()[$k];
                                }
                                $changeLog = new ChangeLog([
                                    'crud' => 'Update',
                                    'information' => $modData->getAttributes(),
                                    'changes' => $changes,
                                    'user_id' => Auth::user()->id,
                                ]);
                                $requisition->changeLogs()->save($changeLog);
                            }
                            $modData->save();
                        }
                    }
                }
                
            }
            //delete
            $deleteTravellersIds = array_diff($currentTravellers, $requisitionTravellerIds);
            foreach ($deleteTravellersIds as $deleteTravellerId) {
                $delData = $travellers->where('id', $deleteTravellerId)->first();
                if ($saveChangeLogs === true) {
                    $changeLog = new ChangeLog([
                        'crud' => 'Delete',
                        'information' => $delData->getAttributes(),
                        'user_id' => Auth::user()->id,
                    ]);
                    $requisition->changeLogs()->save($changeLog);
                }
                $delData->delete();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $travel;
    }


    /**
     * get next/previous selectable trail users
     *
     * @param Travel $travel
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($travel, $sign = 'next')
    {
        $currentFlow = $travel->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;

        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $travel->procurement->unit, $travel->createdUser);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $travel->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
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

        $travel = Travel::findOrFail($id);
        if ($travel->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $travel, $submit);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $travel;
    }

    /**
     * Save document file
     *
     * @param FileRequest $file
     * @param int $id
     * @param string $documentType ['Quotation', 'Invoice', 'Misc']
     * @param boolean $checked True: Quotations by Travels (=checked quotations), False: other all documents (= not checked)
     * @return \App\Travel
     */
    public function saveDocument($file, $id, $documentType, $checked = false)
    {

        if (in_array($documentType, ['Quotation', 'Invoice', 'Misc']) === false) {
            throw new \Exception('wrong document type');
        }

        $travel = Travel::findOrFail($id);

        // Save file
        $filename = uniqid() . '.' . $file->extension();
        $file->storeAs('uploads/documents', $filename, 'public');

        // Save to documents table
        $quotation = new Document([
            'document_type' => $documentType,
            'file_path' => 'uploads/documents/' . $filename,
            'file_name' => $filename,
            'file_extension' => $file->extension(),
            'checked' => $checked == true ? '1' : '0',
            'created_user_id' => AUth::user()->id,
        ]);
        $travel->documents()->save($quotation);

        return $travel;
    }

    /**
     * Delete document file
     *
     * @param int $id
     * @param int $documentId
     * @return \App\Travel
     */
    public function deleteDocument($id, $documentId)
    {

        $travel = Travel::findOrFail($id);
        $document = Document::findOrFail($documentId);

        // Delete file
        Storage::delete('public/' . $document->file_path);

        // Delete from documents table
        $travel->documents()->where('id', $document->id)->delete();

        return $travel;
    }

    /**
     * Delegate
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */
    public function delegate($request, $id)
    {

        $travel = Travel::findOrFail($id);
        if ($travel->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $receiver = User::findOrFail($request->receiver_user_id);

        DB::beginTransaction();
        try {
            // insert delegations table
            $newDelegation = new Delegation([
                'status' => 'Pending',
                'sender_user_id' => Auth::user()->id,
                'receiver_user_id' => $receiver->id,
                'requisition_status_id' => $travel->requisition_status_id,
                'sender_comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $travel->delegations()->save($newDelegation);

            // change current user of the target requisition
            $travel->current_user_id = $receiver->id;
            $travel->save();

            //Update trails status
            $currentTrail = $travel->trails()->where('status', 'CHECKING')->first();
            $currentTrail->update([
                'status' => 'DELEGATING'
            ]);

            //Delete notifications (travel)
            $notifications = $request->user()->notifications()->where([['data->type', 'travel'], ['data->id', $travel->id]])->get();
            if ($notifications) {
                $notifications->markAsRead();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to receiver
        Notification::send($receiver, new TravelDelegateNotification($travel, Auth::user(), $receiver, $request->comment));

        //to owner
        // Mail::to($travel->createdUser->email)->send(new TravelToOwnerMail('delegate', $travel, null, $receiver, null, null));

        DB::commit();

        return $travel;
    }

    /**
     * Finish to delegated task and send message
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */
    public function finishDelegate($request, $id)
    {
        $travel = Travel::findOrFail($id);
        if ($travel->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $delegation = $travel->delegations()->where('status', 'Pending')->first();
        $requester = User::findOrFail($delegation->sender_user_id);

        DB::beginTransaction();
        try {
            // change status on delegations table
            $delegation->status = 'Checked';
            $delegation->checked_at = date("Y-m-d H:i:s");
            $delegation->receiver_comment = $request->comment;
            $delegation->save();

            // --- Travel ---
            // change current user of the target requisition
            $travel->current_user_id = $requester->id;
            $travel->save();

            //Update trails status
            $currentTrail = $travel->trails()->where('status', 'DELEGATING')->first();
            $currentTrail->update([
                'status' => 'CHECKING'
            ]);


            //Delete notifications (travel)
            $notifications = $request->user()->notifications()->where([['data->type', 'travel'], ['data->id', $travel->id]])->get();
            if ($notifications) {
                $notifications->markAsRead();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to receiver
        Notification::send($requester, new TravelFinishDelegateNotification($travel, Auth::user(), $requester, $request->comment));

        DB::commit();

        return $travel;
    }

    /**
     * Delete
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */
    public function delete($request, $id)
    {
        $travel = Travel::findOrFail($id);
        if ($travel->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            //delete uploaded file
            $documents = $travel->documents()->get();
            $requisition = $travel->procurement;

            foreach ($documents as $document) {
                Storage::delete('public/' . $document->file_path);
            }

            //delete requisition
            $travel->delete();
            $requisition->delete();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return null;
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


        $travel = Travel::findOrFail($id);

        if (((in_array($travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== true)
                || ($travel->requisition_status_id == config('const.REQUISITION_STATUS.TRAVEL.CANCELED'))) &&
            !in_array(
                $travel->transport->requisition_status_id,
                [
                    config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_APPROVAL')
                ]
            )
        ) {
            abort(403, 'Unauthorized action.');
        }


        $currentUserIds = [];
        if (!empty($travel->current_user_id)) {
            $currentUserIds[] = $travel->current_user_id;
        }

        DB::beginTransaction();
        try {
            // insert cancel table
            $newCancel = new Cancel([
                'comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $travel->canceled()->save($newCancel);

            //change requisition status & delete current user id
            $travel->requisition_status_id = config('const.REQUISITION_STATUS.TRAVEL.CANCELED');
            $travel->current_user_id = null;
            $travel->save();

            //Delete current trail
            $travel->trails()->where('status', 'CHECKING')->delete();
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
                Notification::send($currentUser, new TravelCancelNotification($travel, Auth::user(), $currentUser, $request->comment));
            }
        }

        //to owner
        if ($travel->created_user_id != Auth::user()->id) {
            Mail::to($travel->createdUser->email)->send(new TravelToOwnerMail('cancel', $travel, null, Auth::user(), null, $request->comment));
        }

        DB::commit();

        //delete notifications for all users (both of travel)
        // Db::table->delete doesn't delete in the DB transaction...
        //DB::table('notifications')->where([['data->type', 'travel'], ['data->id', $travel->id]])->delete();



        return $travel;
    }

    /**
     * Change owner
     *
     * @param Request $request
     * @param int $id
     * @return \Travel
     */
    public function changeOwner($request, $id)
    {
        $travel = Travel::findOrFail($id);

        //Validation
        $request->validate([
            'new_owner_user_id' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        $newOwner = User::findOrFail($request->new_owner_user_id);

        DB::beginTransaction();
        try {
            // change current user of the target requisition
            $travel->created_user_id = $request->new_owner_user_id;
            $travel->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        //to new owner
        Mail::to($newOwner->email)->send(new TravelToOwnerMail('changeOwner', $travel, null, $newOwner, null, $request->comment));

        DB::commit();

        return $travel;
    }


    /**
     * Archive
     *
     * @param Request $request
     * @return \Travel
     */
    public function archive($request)
    {
        $selected = $request->get('selected');
        if (empty($selected)) {
            return false;
        }
        $archiveAction = $request->get('archive_action', "true");

        DB::beginTransaction();
        try {

            foreach (explode(',', $selected) as $id) {
                $travel = Travel::find($id);
                $travel->archived = $archiveAction == "true";
                $travel->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return true;
    }
}

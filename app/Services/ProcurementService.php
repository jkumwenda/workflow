<?php

namespace App\Services;

use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Procurement;
use App\ProcurementItem;
use App\Item;
use App\Flow;
use App\FlowDetail;
use App\User;
use App\Unit;
use App\Trail;
use App\Document;
use App\Delegation;
use App\Cancel;
use App\ChangeLog;

use App\Facades\Requisition;

use App\Mail\Procurement\ProcurementToOwnerMail;
use App\Notifications\Procurement\ProcurementSendNextNotification;
use App\Notifications\Procurement\ProcurementDelegateNotification;
use App\Notifications\Procurement\ProcurementFinishDelegateNotification;
use App\Notifications\Procurement\ProcurementCancelNotification;

use Carbon\Carbon;

class ProcurementService
{
    public function __construct()
    {
    }

    /**
     * Save items
     *
     * If the item does not exist, save in the items table
     *
     * @param string $itemName
     * @return none
     */
    private function saveItem($itemName)
    {
        // Save itemIds
        $count = Item::where('name', trim($itemName))->count();
        if (empty($count)) {
            //Add new Items
            $item = Item::create([
                'name' => trim($itemName),
            ]);
        }
    }

    /**
     * Send Next
     *
     * @param Request $request
     * @param Procurement $procurement
     * @param bool $submit true='submit', false='return'
     */
    public function sendNext($request, Procurement $procurement, bool $submit)
    {
        $next = $this->getNextUsers($procurement, ($submit ? 'next' : 'previous'));

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
        $currentTrail = $procurement->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => $next['flow']->requisition_status_id == config('const.REQUISITION_STATUS.PROCUREMENT.CLOSED') ? 'NORMAL' : 'CHECKING',
        ]);

        $procurement->trails()->save($nextTrail);

        // update procurements
        $procurement->requisition_status_id = $next['flow']->requisition_status_id;
        $procurement->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        
        $procurement->save();

        //Delete notification (procurement)
        //$notifications = $request->user()->notifications()->where([['data->type', 'procurement'], ['data->id', $procurement->id]])->get();
        $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'procurement'")->whereRaw("JSON_VALUE(data, '$.id') = $procurement->id")->get();
        if ($notifications) {
            $notifications->markAsRead();
        }


        //notification & send mails
        //to next user
        if (!empty($next['user'])) {
            if ($submit) {
                Notification::send($next['user'], new ProcurementSendNextNotification('submit', $procurement, $next['flow'], $next['user'], $currentTrail));
                // Mail::to($next['user']->email)->send(new ProcurementSendNextNotification('submit', $procurement, $next['flow'], $next['user'], $currentTrail));
            } else {
                if ($next['user']->id != $procurement->created_user_id) {
                    Notification::send($next['user'], new ProcurementSendNextNotification('return', $procurement, $next['flow'], $next['user'], $currentTrail));
                    // Mail::to($next['user']->email)->send(new ProcurementSendNextNotification('return', $procurement, $next['flow'], $next['user'], $currentTrail));
                }
            }

            //to owner
            if (Auth::user()->id != $procurement->created_user_id) {
                Mail::to($procurement->createdUser->email)->send(new ProcurementToOwnerMail($submit ? 'submit' : 'return', $procurement, $next['flow'], $next['user'], $currentTrail, null));
            }
        }

        return $next;
    }


    /**
     * Create procurement requisition
     *
     * @param ProcurementRequisitionRequest $request
     * @return Procurement $procurement saved data
     */
    public function create($request)
    {

        $moduleId = config('const.MODULE.PROCUREMENT.PROCUREMENT');
        $requisitionStatusId = config('const.REQUISITION_STATUS.PROCUREMENT.REQUISITION_PREPARATION');
        $procurement = null;

        // Save itemIds
        foreach ($request->item_name as $itemName) {
            $this->saveItem($itemName);
        }


        DB::beginTransaction();
        try {

            //procurements
            $procurement = new Procurement([
                'title' => $request->title,
                'unit_id' => $request->unit_id,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
            ]);
              
            $procurement->save();


            // Insert all items
            $itemNames = $request->get('item_name');
            $descriptions = $request->get('description');
            $uoms = $request->get('uom');
            $quantities = $request->get('quantity');
            $saveData = [];
            foreach ($itemNames as $i => $itemName) {
                $item = Item::where('name', trim($itemName))->first();
                $saveData[] = [
                    'procurement_id' => $procurement->id,
                    'item_id' => $item->id,
                    'quantity' => $quantities[$i],
                    'uom' => $uoms[$i],
                    'description' => $descriptions[$i],
                ];
            }
            $procurement->procurementItems()->insert($saveData);

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
            $procurement->trails()->save($trail);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $procurement;
    }


    /**
     * Update procurement requisition
     *
     * @param ProcurementRequisitionRequest $request
     * @return Procurement $procurement updated data
     */
    public function amend($request, $id)
    {
        $procurement = Procurement::findOrFail($id);
        $currentItems = $procurement->procurementItems()->pluck('id')->all();

        // Save itemIds
        foreach ($request->item_name as $itemName) {
            $this->saveItem($itemName);
        }

        // Check need saving change-logs
        $saveChangeLogs = false;
        if (in_array($procurement->unit_id, Auth::user()->units()->pluck('id')->all()) === false) {
            //if other unit user amend, save change logs
            $saveChangeLogs = true;
        }

        DB::beginTransaction();
        try {

            //procurements
            $procurement->title = $request->title;
            $procurement->save();

            // Procurement items
            $itemNames = $request->get('item_name');
            $descriptions = $request->get('description');
            $uoms = $request->get('uom');
            $quantities = $request->get('quantity');
            $procurementItemIds = $request->get('procurement_item_id');


            $saveData = [];
            foreach ($itemNames as $i => $itemName) {

                $item = Item::where('name', trim($itemName))->first();
                $saveData = [
                    'procurement_id' => $procurement->id,
                    'item_id' => $item->id,
                    'quantity' => $quantities[$i],
                    'uom' => $uoms[$i],
                    'description' => $descriptions[$i],
                ];


                if (is_null($procurementItemIds[$i])) {
                    // add
                    $addData = new ProcurementItem($saveData);
                    $procurement->procurementItems()->save($addData);

                    if ($saveChangeLogs === true) {
                        $changeLog = new ChangeLog([
                            'crud' => 'Create',
                            'information' => $addData->getAttributes(),
                            'user_id' => Auth::user()->id,
                        ]);
                        $procurement->changeLogs()->save($changeLog);
                    }
                } else {
                    // modify
                    $modData = $procurement->procurementItems()->where('id', $procurementItemIds[$i])->first();
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
                            $procurement->changeLogs()->save($changeLog);
                        }
                        $modData->save();
                    }
                }
            }

            //delete
            $deleteItemIds = array_diff($currentItems, $procurementItemIds);
            foreach ($deleteItemIds as $deleteItemId) {
                $delData = $procurement->procurementItems()->where('id', $deleteItemId)->first();
                if ($saveChangeLogs === true) {
                    $changeLog = new ChangeLog([
                        'crud' => 'Delete',
                        'information' => $delData->getAttributes(),
                        'user_id' => Auth::user()->id,
                    ]);
                    $procurement->changeLogs()->save($changeLog);
                }
                $delData->delete();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $procurement;
    }


    /**
     * get next/previous selectable trail users
     *
     * @param Procurement $procurement
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($procurement, $sign = 'next')
    {
        $currentFlow = $procurement->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;

        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $procurement->unit, $procurement->createdUser);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $procurement->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
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
        $procurement = Procurement::findOrFail($id);
        if ($procurement->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = null;
        $currentTrail = null;

        DB::beginTransaction();
        try {

            //Send Next
            $next = $this->sendNext($request, $procurement, $submit);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $procurement;
    }

    /**
     * Save document file
     *
     * @param FileRequest $file
     * @param int $id
     * @param string $documentType ['Quotation', 'Invoice', 'Misc']
     * @param boolean $checked True: Quotations by Procurements (=checked quotations), False: other all documents (= not checked)
     * @return \App\Procurement
     */
    public function saveDocument($file, $id, $documentType, $checked = false)
    {

        if (in_array($documentType, ['Quotation', 'Invoice', 'Misc']) === false) {
            throw new \Exception('wrong document type');
        }

        $procurement = Procurement::findOrFail($id);

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
        $procurement->documents()->save($quotation);

        return $procurement;
    }

    /**
     * Delete document file
     *
     * @param int $id
     * @param int $documentId
     * @return \App\Procurement
     */
    public function deleteDocument($id, $documentId)
    {

        $procurement = Procurement::findOrFail($id);
        $document = Document::findOrFail($documentId);

        // Delete file
        Storage::delete('public/' . $document->file_path);

        // Delete from documents table
        $procurement->documents()->where('id', $document->id)->delete();

        return $procurement;
    }

    /**
     * Delegate
     *
     * @param Request $request
     * @param int $id
     * @return \Procurement
     */
    public function delegate($request, $id)
    {
        $procurement = Procurement::findOrFail($id);
        if ($procurement->current_user_id != Auth::user()->id) {
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
                'requisition_status_id' => $procurement->requisition_status_id,
                'sender_comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $procurement->delegations()->save($newDelegation);

            // change current user of the target requisition
            $procurement->current_user_id = $receiver->id;
            $procurement->save();

            //Update trails status
            $currentTrail = $procurement->trails()->where('status', 'CHECKING')->first();
            $currentTrail->update([
                'status' => 'DELEGATING'
            ]);

            //Delete notifications (procurement)
            //$notifications = $request->user()->notifications()->where([['data->type', 'procurement'], ['data->id', $procurement->id]])->get();
            $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'procurement'")->whereRaw("JSON_VALUE(data, '$.id') = $procurement->id")->get();
            if ($notifications) {
                $notifications->markAsRead();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to receiver
        Notification::send($receiver, new ProcurementDelegateNotification($procurement, Auth::user(), $receiver, $request->comment));

        //to owner
        // Mail::to($procurement->createdUser->email)->send(new ProcurementToOwnerMail('delegate', $procurement, null, $receiver, null, null));

        DB::commit();

        return $procurement;
    }

    /**
     * Finish to delegated task and send message
     *
     * @param Request $request
     * @param int $id
     * @return \Procurement
     */
    public function finishDelegate($request, $id)
    {
        $procurement = Procurement::findOrFail($id);
        if ($procurement->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $delegation = $procurement->delegations()->where('status', 'Pending')->first();
        $requester = User::findOrFail($delegation->sender_user_id);

        DB::beginTransaction();
        try {
            // change status on delegations table
            $delegation->status = 'Checked';
            $delegation->checked_at = date("Y-m-d H:i:s");
            $delegation->receiver_comment = $request->comment;
            $delegation->save();

            // --- Procurement ---
            // change current user of the target requisition
            $procurement->current_user_id = $requester->id;
            $procurement->save();

            //Update trails status
            $currentTrail = $procurement->trails()->where('status', 'DELEGATING')->first();
            $currentTrail->update([
                'status' => 'CHECKING'
            ]);

            // --- Purchases ---
            $purchases = $procurement->purchases()->where('current_user_id', Auth::user()->id)->get();
            foreach ($purchases as $purchase) {
                // change current user of the target requisition
                $purchase->current_user_id = $requester->id;
                $purchase->save();

                //Update trails status, current user
                $currentTrail = $purchase->trails()->where('status', 'CHECKING')->first();
                $currentTrail->update([
                    'user_id' => $requester->id
                ]);
            }

            //Delete notifications (procurement)
            //$notifications = $request->user()->notifications()->where([['data->type', 'procurement'], ['data->id', $procurement->id]])->get();
            $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'procurement'")->whereRaw("JSON_VALUE(data, '$.id') = $procurement->id")->get();
            if ($notifications) {
                $notifications->markAsRead();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to receiver
        Notification::send($requester, new ProcurementFinishDelegateNotification($procurement, Auth::user(), $requester, $request->comment));

        DB::commit();

        return $procurement;
    }

    /**
     * Delete
     *
     * @param Request $request
     * @param int $id
     * @return \Procurement
     */
    public function delete($request, $id)
    {
        $procurement = Procurement::findOrFail($id);
        if ($procurement->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            //delete uploaded file
            $documents = $procurement->documents()->get();
            foreach ($documents as $document) {
                Storage::delete('public/' . $document->file_path);
            }

            //delete requisition
            $procurement->delete();
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
     * @return \Procurement
     */
    public function cancel($request, $id)
    {
        $procurement = Procurement::findOrFail($id);


        if (((in_array($procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== true)
            || ($procurement->requisition_status_id == config('const.REQUISITION_STATUS.PROCUREMENT.CANCELED')))) {
            if ($procurement->travel->transport) {
                if (!in_array(
                    $procurement->travel->transport->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_APPROVAL')
                    ]
                )) {
                    dd($procurement->travel->transport->requisition_status_id);
                    abort(403, 'Unauthorized action.');
                }
            } else {
                abort(403, 'Unauthorized action.');
            }
        }


        $currentUserIds = [];
        if (!empty($procurement->current_user_id)) {
            $currentUserIds[] = $procurement->current_user_id;
        }

        DB::beginTransaction();
        try {
            // insert cancel table
            $newCancel = new Cancel([
                'comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $procurement->canceled()->save($newCancel);

            //change requisition status & delete current user id
            $procurement->requisition_status_id = config('const.REQUISITION_STATUS.PROCUREMENT.CANCELED');
            $procurement->current_user_id = null;
            $procurement->save();

            //Delete current trail
            $procurement->trails()->where('status', 'CHECKING')->delete();

            //Update all purchases
            $purchases = $procurement->purchases()->get();
            foreach ($purchases as $purchase) {
                if (!empty($purchase->current_user_id)) {
                    $currentUserIds[] = $purchase->current_user_id;
                }

                //change requisition status & delete current user id
                if ($purchase->route == 'Cheque') {
                    $purchase->requisition_status_id = config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.CANCELED');
                } else {
                    $purchase->requisition_status_id = config('const.REQUISITION_STATUS.PURCHASE_LPO.CANCELED');
                }
                $purchase->current_user_id = null;
                $purchase->save();

                //Delete current trail
                $purchase->trails()->where('status', 'CHECKING')->delete();
            }
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
                Notification::send($currentUser, new ProcurementCancelNotification($procurement, Auth::user(), $currentUser, $request->comment));
            }
        }

        //to owner
        if ($procurement->created_user_id != Auth::user()->id) {
            Mail::to($procurement->createdUser->email)->send(new ProcurementToOwnerMail('cancel', $procurement, null, Auth::user(), null, $request->comment));
        }

        DB::commit();

        //delete notifications for all users (both of procurement, purchases)
        // Db::table->delete doesn't delete in the DB transaction...
        //DB::table('notifications')->where([['data->type', 'procurement'], ['data->id', $procurement->id]])->delete();
        DB::table('notifications')->whereRaw("JSON_VALUE(data, '$.type') = 'procurement'")->whereRaw("JSON_VALUE(data, '$.id') = $procurement->id")->delete();
        $purchaseIds = $procurement->purchases()->pluck('id')->toArray();
        if (!empty($purchaseIds)) {
            //DB::table('notifications')->where('data->type', 'purchase')->whereIn('data->id', $purchaseIds)->delete();
            DB::table('notifications')->whereRaw("JSON_VALUE(data, '$.type') = 'purchase'")->whereRaw("JSON_VALUE(data, '$.id') = $purchase->id")->delete();
        }


        return $procurement;
    }

    /**
     * Change owner
     *
     * @param Request $request
     * @param int $id
     * @return \Procurement
     */
    public function changeOwner($request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        //Validation
        $request->validate([
            'new_owner_user_id' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        $newOwner = User::findOrFail($request->new_owner_user_id);

        DB::beginTransaction();
        try {
            // change current user of the target requisition
            $procurement->created_user_id = $request->new_owner_user_id;
            $procurement->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        //to new owner
        Mail::to($newOwner->email)->send(new ProcurementToOwnerMail('changeOwner', $procurement, null, $newOwner, null, $request->comment));

        DB::commit();

        return $procurement;
    }


    /**
     * Archive
     *
     * @param Request $request
     * @return \Procurement
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
                $procurement = Procurement::find($id);
                $procurement->archived = $archiveAction == "true";
                $procurement->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return true;
    }
}

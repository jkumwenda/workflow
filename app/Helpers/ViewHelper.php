<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


/**
 * Money format, 9,999,999.99
 *
 * @param Number $amount
 * @return String formatted number
 *
 * @author Sayuri.Tsuboi
 */
if (! function_exists('moneyFormatter'))
{
    function moneyFormatter($amount) {
        return number_format(round($amount, 2), 2);
    }
}

/**
 * Make trail column template
 *
 * @param Number $i trail number
 * @param Object $nextTrail
 * @param Object $trail
 * @param bool $sendableMessage display sending message button or not
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if (! function_exists('trailTableColumn'))
{
    function trailTableColumn($i, $trail, $sendableMessage = false) {
        $iconName = '';
        $requisitionStatus = '';
        $roleName = '';
        if (class_basename($trail) == 'Cancel') {
            $iconName = "ban";
            $requisitionStatus = 'Canceled';
            $roleName = '';
            $date = $trail->created_at;
        } else {
            if ($trail->status == 'NORMAL') {
                $iconName = 'check';
            } else if ($trail->status == 'RETURNED'){
                $iconName = 'reply';
            } else if ($trail->status == 'TRANSFERRED') {
                $iconName = 'random';
            }
            $flowDetail = $trail->flowDetail;
            $requisitionStatus = !empty($flowDetail) ? $flowDetail->requisitionStatus->name : '';
            $role = !empty($flowDetail) ? $flowDetail->role : null;
            $roleName = !empty($role) ? $role->description : '';
            $date = !empty($trail->transaction_at) ? $trail->transaction_at : '';
        }

        $icon ="<i class='fa fa-$iconName' aria-hidden='true'></i>";
        $comment = "";
        if(!empty($trail->comment)){
            $tmp = nl2br($trail->comment);
            $comment = "&nbsp;<i class='trail-comment'>{$tmp}</i>";
        }

        $td = '';
        if (!empty($trail->user)) {
            // with User Name
            $userId = !empty($trail->user) ? $trail->user->id : '';
            $userName = !empty($trail->user) ? $trail->user->name : '';
            if ($trail->status == 'DELEGATING') {
                $userName .= " (DELEGATING)";
            }
            $sendMessageButton = '';
            if ($sendableMessage == true) {
                $sendMessageButton = sprintf('<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm btn-just-icon" data-user-id="%s" data-user-name="%s" data-user-description="%s" data-toggle="modal" data-target="#sendMessageModal"><i class="fa fa-envelope"></i></a> <br/>', $userId, $userName, $roleName);
            }

            $td = <<<EOT
                    <tr>
                    <td>$i</td>
                    <td>
                        <span class="icon">$icon</span>
                        <div class="info">
                            <small>$roleName</small><br/>
                            <strong>$userName</strong>&nbsp;
                            $sendMessageButton
                            <small><i>$requisitionStatus</i></small>
                            <div>$date</div>
                            $comment
                        </div>
                    </td>
                    </tr>
                    EOT;
        } else {
                // no users
                $td = <<<EOT
                <tr>
                <td>$i</td>
                <td>
                    <span class="icon">$icon</span>
                    <div class="info">
                        <strong><i>$requisitionStatus</i></strong><br/>
                        <div>$date</div>
                        $comment
                    </div>
                </td>
                </tr>
                EOT;
        }

        return $td;
    }
}




/**
 * Show supported documents
 *
 * @param Array $documents
 * @param String $docType "quote" or "doc" only
 * @param Boolean deletable if this is true, show remove buttons
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('listSupportedDocuments'))
{
    function listSupportedDocuments($documents, $docType = '', $deletable = false) {
        $returnHtml = '';
        if ($documents->isEmpty()) {
            $returnHtml = '<div style="height: 50px;">No files</div>';
        }

        foreach($documents as $num => $document){
            $icon = 'img.png';
            switch ($document->file_extension) {
                case 'doc':
                case 'docx':
                    $icon = 'doc.png';
                    break;
                case 'pdf':
                    $icon = 'pdf.png';
                    break;
            }

            $returnHtml .= '<div class="col-sm-2 alert">';
            $returnHtml .= sprintf(
                '<a target="_blank" href="%s"><img src="%s" width="50px" alt="" class="img-thumbnail"></a><br/>',
                Storage::url($document->file_path),
                '/images/icons/' . $icon
            );
            $returnHtml .= sprintf("%s %s", (!empty($docType) ? $docType : $document->document_type), ($num + 1));

            if ($deletable == true) {
                $returnHtml .= '<a data-toggle="modal" data-target="#confirm_document_delete" href="javascript: void(0);" class="btn btn-sm btn-default" data-document-id="' . $document->id . '"><i class="fa fa-trash"></i></a>';
            }
            $returnHtml .= '</div>';
            $returnHtml .= "\n";
        }

        return $returnHtml;
    }
}


/**
 * Show Q and A list
 *
 * @param Array $messages
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('listQA'))
{
    function listQA($messages) {

        $returnHtml = '';
        $returnHtml .= '<div class="list-group list-group-flush">';

        foreach ($messages as $message) {
            $returnHtml .= sprintf('<span class="list-group-item flex-column align-items-start">');
            $returnHtml .= sprintf('<div class="d-flex w-100 justify-content-between">');
            $returnHtml .= sprintf('<h5 class="mb-1">%s</h5>', nl2br($message->question));
            $returnHtml .= sprintf('<small>%s &nbsp;&rarr;&nbsp; %s ( %s )</small>', $message->questioner->name, $message->receiver->name, $message->created_at);
            $returnHtml .= sprintf('</div>');

            if (empty($message->answer)) {
                $returnHtml .= sprintf('<a href="#" class="btn btn-sm btn-outline-secondary" data-message-id="%s" data-user-name="%s" data-question="%s" data-toggle="modal" data-target="#answerMessageModal"> <i class="fa fa-envelope"></i> Reply</a>', $message->id, $message->questioner->name, $message->question);
            } else {
                $returnHtml .= sprintf('<p class="mb-1"> %s </p>', nl2br($message->answer));
                $returnHtml .= sprintf('<small><i> %s &nbsp; ( %s )</i></small>', $message->answerer->name, $message->updated_at);
            }
            $returnHtml .= sprintf('</span>');
        }
        $returnHtml .= '</div>';
        $returnHtml .= "\n";

        return $returnHtml;

    }
}


/**
 * Get procurement current locations
 *
 * @param Array $trails procurement requisition trail array
 * @param Array $purchases purchase requisition trail array
 * @param Array $delegations
 * @param Array $canceled
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('getProcurementCurrentLocation'))
{
    function getProcurementCurrentLocation($trails, $purchases, $delegations, $canceled) {

        $currentLocation = [];

        if (!$canceled->isEmpty()) {
            $currentLocation[] = 'Canceled';

        } else {

            $lastTrail = $trails->last();
            if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
                $interval = difference_dates($lastTrail->created_at);
                if (!$delegations->isEmpty()) {
                    $currentLocation[] = sprintf("Delegated to %s (%d day(s))", $delegations[0]->receiver->name, $interval);
                } else if (!empty($lastTrail->user)) {
                    $currentLocation[] = sprintf("%s's office (%d day(s))", $lastTrail->user->name, $interval);
                }
            }

            if (!$purchases->isEmpty()) {
                //purchase trails
                foreach ($purchases as $purchase) {
                    if (!empty($purchaseLastTrail->user)) {
                        $supplier = $purchase->supplier->name;
                        $purchaseLastTrail = $purchase->trails()->latest()->first();

                        $interval = difference_dates($purchaseLastTrail->created_at);
                        $currentLocation[] = sprintf("%s' office (%d day(s)) - %s", $purchaseLastTrail->user->name, $interval, $supplier);
                    }
                }
            }
        }

        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation,);
    }
}



/**
 * Get purchase current locations
 *
 * @param Array $trail purchase requisition trail array
 * @param Array $delegated
 * @param Array $purchaseTrail purchase requisition trail array
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('getPurchaseCurrentLocation'))
{
    function getPurchaseCurrentLocation($trails, $delegations, $canceled) {

        $currentLocation = [];

        if (!$canceled->isEmpty()) {
            $currentLocation[] = 'Canceled';

        } else {

            $lastTrail = $trails->last();
            if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
                $interval = difference_dates($lastTrail->created_at);
                if (!$delegations->isEmpty()) {
                    $currentLocation[] = sprintf("Delegated to %s (%d day(s))", $delegations[0]->receiver->name, $interval);
                } else if (!empty($lastTrail->user)) {
                    $currentLocation[] = sprintf("%s' office (%d day(s))", $lastTrail->user->name, $interval);
                }
            }
        }

        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation);
    }
}

/**
 * Get purchase current locations
 *
 * @param Array $trail purchase requisition trail array
 * @param Array $delegated
 * @param Array $purchaseTrail purchase requisition trail array
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('getTravelCurrentLocation'))
{
    function getTravelCurrentLocation($trails, $delegations, $canceled) {

        $currentLocation = [];

        if (!$canceled->isEmpty()) {
            $currentLocation[] = 'Canceled';

        } else {

            $lastTrail = $trails->last();
            if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
                $interval = difference_dates($lastTrail->created_at);
                if (!$delegations->isEmpty()) {
                    $currentLocation[] = sprintf("Delegated to %s (%d day(s))", $delegations[0]->receiver->name, $interval);
                } else if (!empty($lastTrail->user)) {
                    $currentLocation[] = sprintf("%s' office (%d day(s))", $lastTrail->user->name, $interval);
                }
            }
        }
        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation);
    }
}

if ( ! function_exists('getTransportCurrentLocation'))
{
    function getTransportCurrentLocation($trails, $delegations, $canceled) {

        $currentLocation = [];

        if (!$canceled->isEmpty()) {
            $currentLocation[] = 'Canceled';

        } else {

            $lastTrail = $trails->last();
            if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
                $interval = difference_dates($lastTrail->created_at);
                if (!$delegations->isEmpty()) {
                    $currentLocation[] = sprintf("Delegated to %s (%d day(s))", $delegations[0]->receiver->name, $interval);
                } else if (!empty($lastTrail->user)) {
                    $currentLocation[] = sprintf("%s' office (%d day(s))", $lastTrail->user->name, $interval);
                }
            }
        }
        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation);
    }
}

if ( ! function_exists('getSubsistenceCurrentLocation'))
{
    function getSubsistenceCurrentLocation($trails, $delegations, $canceled) {

        $currentLocation = [];

        if (!$canceled->isEmpty()) {
            $currentLocation[] = 'Canceled';

        } else {

            $lastTrail = $trails->last();
            if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
                $interval = difference_dates($lastTrail->created_at);
                if (!$delegations->isEmpty()) {
                    $currentLocation[] = sprintf("Delegated to %s (%d day(s))", $delegations[0]->receiver->name, $interval);
                } else if (!empty($lastTrail->user)) {
                    $currentLocation[] = sprintf("%s' office (%d day(s))", $lastTrail->user->name, $interval);
                }
            }
        }
        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation);
    }
}

/**
 * Get voucher current locations
 *
 * @param Array $trail voucher requisition trail array
 * @return String html
 *
 * @author Sayuri.Tsuboi
 */
if ( ! function_exists('getVoucherCurrentLocation'))
{
    function getVoucherCurrentLocation($trails) {

        $currentLocation = [];

        $lastTrail = $trails->last();
        if (!in_array($lastTrail->status, ['NORMAL', 'RETURNED'])) {
            $interval = difference_dates($lastTrail->created_at);
            $currentLocation[] = sprintf("%s' office (%d day(s))", $lastTrail->user->name, $interval);
        }

        if (empty($currentLocation)) {
            $currentLocation[] = '-';
        }

        return join('<br>',$currentLocation);
    }
}

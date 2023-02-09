<?php

if (! function_exists('pdfTrailTableColumn'))
{
    //accessing inline css classes for icons from "transport-approval-document.blade.php"
    function getCssClass($trail): string
    {
        switch ($trail->status) {
            case 'NORMAL':
                return 'check';
            case 'RETURNED':
                return 'reply';
            case 'TRANSFERRED':
                return 'random';
            case 'Cancel':
                return 'ban';
            default:
                return 'pending';
        }
    }

    function pdfTrailTableColumn($i, $trail): string
    {
        $className = "";
        if (class_basename($trail) == 'Cancel') {
            $className = getCssClass($trail);
            $requisitionStatus = 'Canceled';
            $roleName = "";
            $date = $trail->created_at;
        } else {
            if ($trail->status == "NORMAL") {
                $className = getCssClass($trail);
            } else if ($trail->status == "RETURNED"){
                $className = getCssClass($trail);
            } else if ($trail->status == 'TRANSFERRED') {
                $className = getCssClass($trail);
            } else {
                $className = getCssClass($trail);
            }
            $flowDetail = $trail->flowDetail;
            $requisitionStatus = !empty($flowDetail) ? $flowDetail->requisitionStatus->name : '';
            $role = !empty($flowDetail) ? $flowDetail->role : null;
            $roleName = !empty($role) ? $role->description : '';
            $date = !empty($trail->transaction_at) ? $trail->transaction_at : '';
        }

        $icon ="<i class='$className' aria-hidden='true'></i>";
        $comment = "";
        if(!empty($trail->comment)){
            $tmp = nl2br($trail->comment);
            $comment = "&nbsp;<i class='trail-comment'>{$tmp}</i>";
        }

        if (!empty($trail->user)) {
            // with Username
            $userName = !empty($trail->user) ? $trail->user->name : '';
            if ($trail->status == 'DELEGATING') {
                $userName .= " (DELEGATING)";
            }

            $td = <<<EOT
                <tr>
                    <td>$i</td>
                    <td> $userName </td>
                    <td> $roleName </td>
                    <td> $requisitionStatus </td>
                    <td>
                        <span >$icon</span>
                    </td>
                    <td> $comment </td>
                    <td> $date </td>
                </tr>
                EOT;
        } else {
             // no users
             $td = <<<EOT
                <tr>
                   <td> $requisitionStatus </td>
                   <td>  </td>
                   <td>  </td>
                   <td>  </td>
                   <td>  </td>
                   <td>  </td>
                   <td>  </td>
               </tr>
             EOT;
        }
        return $td;
    }
}
?>

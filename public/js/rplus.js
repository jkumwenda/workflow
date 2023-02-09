
function processingModal() {
    $('#processing-modal').modal();
}

function clone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

function copyText($contentId) {
    var copyFrom = document.createElement('textarea');
    copyFrom.textContent = document.getElementById($contentId).textContent;
    var bodyElm = document.getElementsByTagName('body')[0];
    bodyElm.appendChild(copyFrom);
    copyFrom.select();
    document.execCommand('copy');
    bodyElm.removeChild(copyFrom);
}

function nl2br(str) {
    str = str.replace(/\r\n/g, "<br />");
    str = str.replace(/(\n|\r)/g, "<br />");
    return str;
}

$(document).ready(function() {
    //tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //popover
    $('[data-toggle="popover"]').popover();

    //select2
    $(".select2").select2();

    //datatable
    $(".datatable").DataTable({
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 50,
        "stateSave": true
    });

    //delete deleteModal
    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var item = button.data('item') // action
        var modal = $(this)
        modal.find('.modal-footer #deleteForm').attr("action", item)
    });

    //confirm confirmModal
    $('#confirmModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var item = button.data('item') // action
        var modal = $(this)
        modal.find('.modal-footer #confirmForm').attr("action", item)
    });

    //send message
    $('#sendMessageModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var passedData = button.data();

        var form = $(this).find('#sendMessageForm');
        form.find(".name").text(passedData.userName);
        form.find(".description").text(passedData.userDescription);
        form.find("[name='receiver']").val(passedData.userId);
        form.find("[name='messageable_type']").val($('#able_type').val());
        form.find("[name='messageable_id']").val($('#able_id').val());
    });

    //answer message
    $('#answerMessageModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var passedData = button.data();

        var form = $(this).find('#answerMessageForm');
        form.find(".name").text(passedData.userName);
        form.find(".question").html(nl2br(passedData.question));
        form.find("[name='message_id']").val(passedData.messageId);
    });

    //check notifications
    if(Laravel.userId) {
        var checkNotification = function () {
            $.get('/notifications/check', function (data) {
                //notification
                var notification = '';
                if (data.notification.count) {
                    for (var i = 0; i < data.notification.latest.length; i++) {
                        notification +=
                            '<div class="dropdown-item">' +
                            '<a href="' + data.notification.latest[i].url + '">' +
                            '<div><i class="fa rplus-icon-' + data.notification.latest[i].type + '"></i> ' + data.notification.latest[i].type + ' - # ' + data.notification.latest[i].formattedId + '</div>' +
                            '<small class="text-dark text-wrap">' + data.notification.latest[i].notification + '</small>' +
                            (data.notification.latest[i].comment ? ('<div class="trail-comment text-wrap">' + nl2br(data.notification.latest[i].comment) + '</div>') : '') +
                            '</a>' +
                            '</div>' +
                            '<div class="dropdown-divider"></div>';
                    }
                } else {
                    notification = '<div class="dropdown-header">No notifications</div><div class="dropdown-divider"></div>';
                }
                $('#notificationsMenu').html($(notification));
                $('#notification-counter').html(data.notification.count);
                $('#notification-counter').parent().removeClass('badge-danger').removeClass('badge-secondary').addClass('badge-' + (data.notification.count ? 'danger' : 'secondary'));

                //to be confirmed
                $('#confirmed-counter').html(data.confirmed.count);
                $('#confirmed-counter').parent().removeClass('badge-danger').removeClass('badge-secondary').addClass('badge-' + (data.confirmed.count ? 'danger' : 'secondary'));

            });
        };


        checkNotification();
        var tid = setInterval(function() {
            checkNotification();
        }, 60000);
    }

    /*******************
     * Rplus original functions
     ******************* */
    ;(function($) {
        var $self = null;

        // Rplus Item Table
        $.fn['itemTable'] = function(options, fire) {
            options = options || {};

            $self = $(this);
            initButtons();
            setRowNumber();
            initDeleteRow();
            initInsertRow();
            setMinDate();
            setDepartureDate();
            setReturnDate();
            toggleSelectedItem();

            return {
                setRowNumber: function() {
                    setRowNumber();
                }
            };
        }

        // Add Insert / Delete button
        function initButtons() {
            // Add Delete button for each lines
            $('<th />').insertAfter($self.find('thead > tr > th:last'));
            var buttonTd = $('<td class=""><a class="btn btn-xs btn-round btn-danger text-white js-delete-row"><i class="fa fa-times"></i></a></td>');
            $self.find('tbody > tr').each(function() {
                //:not([data-no-delete="true"])
                if ($(this).data('no-delete') && $(this).data('no-delete') == true) {
                    $('<td />').insertAfter($(this).find('td:last'));
                } else {
                    buttonTd.clone().insertAfter($(this).find('td:last'));
                }
            });

            // Add Insert button to last line
            var colNum = $self.find('tr:last > td').length;
            var buttonTr = $('<tr><td class="" colspan="' + colNum + '"><a class="btn btn-default btn-round btn-xs text-white btn-success" id="add-row"><i class="fa fa-plus"></i> Add</a></td></tr>');
            buttonTr.insertAfter($self.find('tr:last'));
        }

        // Auto set row number in table
        function setRowNumber() {
            $self.find('td.js-row-no').each(function (i) {
                i = i + 1;
                $(this).text(i);
            });
        };

        //set min date constraint
        function setMinDate() {
            $('#departureDate').attr('min', new Date().toISOString().split('T')[0]);
            $('#returnDate').attr('min', new Date().toISOString().split('T')[0]);
            $('#userDepartureDate').attr('min', new Date().toISOString().split('T')[0]);
            $('#userReturnDate').attr('min', new Date().toISOString().split('T')[0]);
        }

        // Auto set departure date in table
        function setDepartureDate() {
            $("#departureDate").on("change", function(){
                var value = $(this).val();
                $("#userDepartureDate").val(value);
            });
        };

        // Auto set return date in table
        function setReturnDate() {
            $("#returnDate").on("change", function(){
                var value = $(this).val();
                $("#userReturnDate").val(value);
            });
        };

        // Show and hide fields based on radio button selection
        function toggleSelectedItem() {
            $("#radioButton1").on("click", function(){
                $("#unitField").removeClass('d-none');          //to show field
                $("#unitField2").addClass('d-none');            //to hide field
            });
            $("#radioButton2").on("click", function(){
                $("#unitField").addClass('d-none');             //to hide field
                $("#unitField2").removeClass('d-none');         //to show field
            });
            $("#radioButton3").on("click", function(){
                $("#unitField").addClass('d-none');             //to hide field
                $("#unitField2").addClass('d-none');            //to hide field
            });
        }

        // delete row
        function initDeleteRow() {
            $self.on('click', '.js-delete-row', function() {
                if (countRow() > 1) {
                    $(this).closest("tr").remove();
                    setRowNumber();
                } else {
                    resetFields($(this).closest("tr"));
                }
            });
        }

        // insert row
        function initInsertRow() {
            $self.on('click', '#add-row', function() {
                var insertRow = $(this).closest("tbody").find('tr:first').clone();
                resetFields(insertRow);
                insertRow.insertBefore($(this).closest('tr'));
                setRowNumber();
                setDepartureDate();
                setReturnDate();
            });
        }

        function countRow() {
            return $self.find('tbody > tr').length - 1;
        }

        function resetFields($c) {
            $c.find('select, textarea').each(function() { //input is not reset
                $(this).val('');
            });
            $c.find('#hiddenTravellerInput').each(function() { //input is not reset
                $(this).val(null);
            });
        }

    })(window.jQuery);
});

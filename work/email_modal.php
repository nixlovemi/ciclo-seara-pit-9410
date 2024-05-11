<?php
include_once('session.php');
include_once('functions.php');

function formHtml($arrExport)
{
    $html = '';
    foreach($arrExport as $item) {
        $number = $item['menuIdx'] + 1;
        $idx = $item['menuIdx'];
        $produto = $item['product'];
        $thumb = $item['export']['thumb'];

        $html .= <<<HTML
            <div class="col-md-4 dv-thumb-export">
                <div class="custom-control custom-checkbox image-checkbox">
                    <input
                        type="checkbox"
                        class="custom-control-input"
                        data-idx="$idx"
                        id="ck$idx"
                    />
                    <label class="custom-control-label" for="ck$idx">
                        <img src="$thumb" alt="#" class="img-fluid" title="$produto">
                    </label>
                </div>
            </div>
        HTML;
    }

    return $html;
}

$config = $_SESSION['config'];
$arrExport = array_filter($config['pages'], function($val) {
    return is_array($val['export']);
});

if (isset($_GET['form']) && $_GET['form'] == 1) {
    echo formHtml($arrExport);
    return;
}
if (isset($_POST['send']) && $_POST['send'] == 1) {
    $ids = $_POST['ids'];
    $email = $_POST['email'];
    $allPages = $_POST['allPages'];

    $arrFiles = [];
    if ($allPages == true) {
        $arrFiles[] = $config['allPagesPdfUrl'];
    } else {
        $arrSend = array_filter($arrExport, function($val) use ($ids) {
            return in_array($val['menuIdx'], $ids);
        });
        foreach($arrSend as $item) {
            $arrFiles[] = $item['export']['file'];
        }
    }

    $body = 'Olá<br /><br />';
    $body .= 'Obrigado pela sua visita no APAS2024.<br />';
    $body .= 'Segue em anexo as informações referente ao(s) produto(s) selecionado(s).<br /><br />';
    $body .= 'Atenciosamente, equipe Seara.';

    echo sendEmail($email, 'Seara - APAS2024', $body, $arrFiles);
    return;
}
?>

<div class="modal" tabindex="-1" role="dialog" id="emailModal">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar material</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div id="sending-body" class="modal-body" style="display:none;">
                <div style="text-align:center">
                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                    Enviando material. Aguarde, por favor ...
                </div>
            </div>

            <div id="main-body" class="modal-body">
                <div
                    class="alert alert-warning fade show"
                    role="alert"
                    style="display:none;"
                    id="alert-msg"
                >
                    <span id="message">
                        Erro!
                    </span>
                    <button type="button" class="close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <input
                            type="email"
                            class="form-control"
                            name="inputEmail"
                            id="inputEmail"
                            placeholder="E-mail ..."
                            value=""
                        />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="seara custom-control-input" id="allPages">
                            <label class="seara custom-control-label" for="allPages">Catálogo Completo</label>
                        </div>
                    </div>
                </div>
                <div class="row export-items">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-send submit">
                    <i class="fa fa-envelope-o" aria-hidden="true"></i>
                    Enviar
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showAlert(msg)
    {
        $('#alert-msg #message').html(`<b>Atenção</b>: ${msg}`);
        $('#alert-msg').fadeIn(350);
        $('#emailModal').animate({ scrollTop: 0 }, 350);

        setTimeout(() => {
            closeAlert();
        }, 4000);
    }

    function closeAlert()
    {
        $('#alert-msg').fadeOut(350);
    }

    $(document).on('click', '#alert-msg button.close', function(e) {
        closeAlert();
    });

    function validateEmail(email)
    {
        let validRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return validRegex.test(email);
    }

    // show.bs.modal, shown.bs.modal, hide.bs.modal, hidden.bs.modal
    $('#emailModal').on('shown.bs.modal', function (e) {
        // vars
        // let currentPage = $('#dv-container').find('ul.menu-toc li.clickable.menu-toc-current').data('idx');
        let currentPage = $('#dv-container').find('ul.menu-toc .clickable.menu-toc-current').data('idx');
        let $exportItens = $('div.export-items');

        setTimeout(() => {
            $.get("email_modal.php?form=1", function(data, status) {
                $exportItens.html('<p style="text-align:center; width:100%;">Carregando ...</p>');
                $exportItens.html(data);

                setTimeout(() => {
                    $('#emailModal #allPages').prop('checked', false);
                    $('#emailModal .export-items').show(200);
                    $('#emailModal input#inputEmail').val('');
                    let $imageCheckbox = $exportItens.find('div.image-checkbox');
                    $imageCheckbox.find(`input[data-idx='${currentPage}']`).parent().find('label').trigger('click');
                }, 175);
            });
        }, 200);
    });

    $('#emailModal').on('hide.bs.modal', function (e) {
        // re-display items
        $('#emailModal .modal-footer .submit').show();
        $('#emailModal #sending-body').hide();
        $('#emailModal #main-body').show();
    });

    $(document).on('click', '#emailModal .modal-footer button.submit', function(e) {
        let email = $(this).closest('div.modal-content').find('input#inputEmail').val();
        if (email == '') {
            showAlert('Preencha o e-mail!');
            return;
        }

        if (false === validateEmail(email)) {
            showAlert('Informe um e-mail válido!');
            return;
        }

        let allPages = $('#emailModal #allPages').is(':checked');
        let items = $(this).closest('div.modal-content').find('input:checked');
        if (items.length <= 0 && !allPages) {
            showAlert('Nenhum produto selecionado!');
            return;
        }

        closeAlert();

        let idxs = [];
        items.each(function(idx, item){
            idxs.push($(item).data('idx'));
        });

        // send
        $.ajaxSetup({
            beforeSend: function(){
                $('#emailModal #main-body').hide();
                $('#emailModal #sending-body').show();
                $('#emailModal .modal-footer .submit').hide();
            },
            complete: function(data) {
                // clean setup
                $.ajaxSetup({
                    beforeSend: null,
                    complete: true
                });

                let response = data.responseText;
                let statusCode = data.status;
                let errorMsg = '';
                
                if (statusCode != 200) {
                    errorMsg = 'O servidor registrou um erro ao enviar o e-mail. Tente novamente mais tarde!';
                } else if (response == 0) {
                    errorMsg = 'Erro ao enviar o e-mail. Tente novamente mais tarde!';
                }

                if (errorMsg != '') {
                    $('#emailModal .modal-footer .submit').show();
                    $('#emailModal #sending-body').hide();
                    $('#emailModal #main-body').show();

                    showAlert(errorMsg);
                    return;
                }

                // all good
                $('#emailModal #modal-footer #submit').hide();
                $('#emailModal #main-body').hide();
                $('#emailModal #sending-body div').html('<i style="color:#4BB543" class="fa fa-check" aria-hidden="true"></i> Email enviado com sucesso!');
                $('#emailModal #sending-body').show();
            }
        });

        $.post("email_modal.php", {'send': 1, email: email, 'ids': idxs, 'allPages': (allPages) ? 1: 0}, function(result) { });
    });

    $(document).on('change', '#emailModal #allPages', function(e) {
        let isChecked = $(this).is(':checked');
        let $exportItems = $('#emailModal .export-items');

        if (isChecked) {
            $exportItems.hide(200);
        } else {
            $exportItems.show(200);
        }
    });
</script>
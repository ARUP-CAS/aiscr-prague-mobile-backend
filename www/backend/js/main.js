$(document).ready(function(){
    var allEditors = document.querySelectorAll('.ckeditor')
    for (var i = 0; i < allEditors.length; ++i) {
        CKEDITOR.replace(allEditors[i])
    }


	$('.flash').delay(3000).fadeOut(500);

    $('.chosen').chosen();

    $('#tabs').tabs();

	/* delete item confirm */
    $("ul li a.action.remove").click( function() {
        if(confirm("Opravdu si přejete smazat tuto položku?"))
            return true;
        return false;
    });

    $('input[type="number"]').keydown(function (e) {
        console.log(e.key);
        if(e.key=='e' || e.key=='-')
            return false;
    });

    //název souboru u file inputu
    $('.input.upload .file input[type=file]').change(function(){
        $(this).parents('.input.upload').find('.text .n2').text( this.value );
    });

    // delete foto
    $('.gallery li .controls .remove').click(function(){
        if(confirm("Opravdu si přejete smazat tuto položku?")){
            $this = $(this);

            $.ajax({
                type: 'GET',
                url: $this.attr('href'),
                success:function(data){
                    console.log('OK');
                    $this.closest('li').fadeOut('slow', function(){
                        $this.closest('li').remove();
                    });
                },
                error:function(){
                    console.log( "The End." );
                }
            });

            return false;
        }
        return false;
    });

    // date and time picker
    $('input.datetime').datetimepicker({
		language:  'cs',
		weekStart: 1,
		todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
		showMeridian: 1,
        format: 'dd.mm.yyyy hh:ii'
	});

    // date and time picker
    $('input.date').datetimepicker({
        language:  'cs',
        weekStart: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 0,
        showMeridian: 'day',
        format: 'dd.mm.yyyy'
    });

}); // document.ready

// uploadify init
function uploadifyUpload(id){
	$this = $(id);
	$(id+' .uploadify-upload').uploadify({
        //'fileSizeLimit' : '4MB',
        'buttonText'    : 'VYBRAT SOUBORY',
        //'fileTypeExts'  : '*.jpg;',
        //'debug'         : true,
        'width'         : 184,
        'height'        : 48,
        'swf'           : $('body').data('basepath')+'/backend/js/uploadify/uploadify.swf',
        'uploader'      :  $('body').data('basepath')+'/backend/js/uploadify/uploadify.php',//{!$basePath}/js/uploadify/uploadify-photogallery.php
        'onUploadError' : function(file, errorCode, errorMsg, errorString) {
            alert('Soubor ' + file.name + ' se nepodařilo nahrát: ' + errorString);
        },
        'onUploadSuccess' : function(file, data, response) {
            //alert('The file ' + file.name + ' was successfully uploaded with a response of ' + response + ' : ' + data);
            $this.find('textarea').val($this.find('textarea').val()+data+",");
            var pocet = parseInt($this.find('.text .n3 strong').text());
            $this.find('.text .n3 strong').text(pocet+1);
        }
        // Put your options here
    });
}

/**
 * Czech translation for bootstrap-datetimepicker
 * Matěj Koubík <matej@koubik.name>
 * Fixes by Michal Remiš <michal.remis@gmail.com>
 */
;(function($){
	$.fn.datetimepicker.dates['cs'] = {
		days: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
		daysShort: ["Ned", "Pon", "Úte", "Stř", "Čtv", "Pát", "Sob", "Ned"],
		daysMin: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		monthsShort: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čer", "Čnc", "Srp", "Zář", "Říj", "Lis", "Pro"],
		today: "Dnes",
		suffix: [],
		meridiem: [],
		weekStart: 1,
		format: "dd.mm.yyyy HH:ii:ss"
	};
}(jQuery));


function initDropzone(name, accept){
    var submit_text = null;
    $(name).dropzone({
        //options
        url: window.location.href+'&do=dropzone-saveFile',
        autoProcessQueue: true,
        addRemoveLinks: true,
        maxFiles: 20,
        maxFilesize: 1024,
        acceptedFiles: accept,
        success: function(file) {
            console.log(file.xhr.response);

            //add to log
            $(name.replace('upload-', '')).val( $(name.replace('upload-', '')).val() + ',' + file.xhr.response);
        },
        removedfile: function(file) {
            //remove from server
            $.ajax({
                url: window.location.href+'&do=dropzone-removeFile',
                data: 'filename=' + file.xhr.response
            });
            // remove from log
            $(name.replace('upload-', '')).val( $(name.replace('upload-', '')).val().replace(file.xhr.response, ''));

            var _ref;
            return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
        },
        totaluploadprogress: function(){
            if(submit_text==null)
                submit_text = $(name).closest('form').find('input[type="submit"]').val();
            $(name).closest('form').find('input[type="submit"]').val('nahrávám...');
            $(name).closest('form').find('input[type="submit"]').attr('disabled', '');
        },
        queuecomplete: function(e){
            $(name).closest('form').find('input[type="submit"]').val(submit_text);
            $(name).closest('form').find('input[type="submit"]').removeAttr('disabled');
            submit_text = null;
        },

        //translation
        dictDefaultMessage: 'Přetáhněte sem soubory nebo klikněte na tlačítko níže',
        dictFallbackMessage:'Váš prohlížeč nepodporuje upload stylem Drag & Drop',
        dictInvalidFileType:'Tento typ souboru není povolený.',
        dictFileTooBig: 'Soubor je příliš velký.',
        dictResponseError:'Při nahrávání nastala chyba.',
        dictCancelUpload:'Přerušit nahrávání',
        dictCancelUploadConfirmation:'Opravdu chcete přerušit nahrávání?',
        dictRemoveFile:'Odstranit',
        dictMaxFilesExceeded:'Snažíte se nahrát příliš mnoho souborů',
    });
}
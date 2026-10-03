(() => {

    'use strict';

	// identificadores HTML
	const
        table_message_body  = document.querySelector('#message-board tbody'),
        noMessagesDiv       = document.querySelector('#aviso-no-moderation'),
        moderationLinks     = document.querySelector('#message-board >caption'),
        preview            = document.querySelector('#carga-externa'),



        ajax_loader			= document.querySelector('#carga-externa'),
        closebutton         = document.querySelector('#sjbboard .closebutton');




    /*
     _____                          _
    |  ___|  _   _   _ __     ___  (_)   ___    _ __    ___
    | |_    | | | | | '_ \   / __| | |  / _ \  | '_ \  / __|
    |  _|   | |_| | | | | | | (__  | | | (_) | | | | | \__ \
    |_|      \__,_| |_| |_|  \___| |_|  \___/  |_| |_| |___/

    */


    const cambiarStatusMensaje = (tr, status)=>{

        //resetPreloader();
        //const id = tr.id;
        //const tr.dataset.id);
        console.log(tr);
        //const message_id =  (tr.dataset.id);
        let data2Send = {
            action          : SJB_BOARD.ajax_action,
            sjb_noncename   : SJB_BOARD.ajax_nonce,
            quefasemos      : 'approvemessage',
            message_id      : parseInt(tr.dataset.id, 10),
            status          : status
        }

        // console.log(data2Send);
        // return;
        //showPreloader();

        jQuery.ajax({
            url: SJB_BOARD.ajaxurl ,
            dataType: "json",
            type: 'post',
            cache: false,
            data: data2Send,

            success: function ( jsonData ) {
                console.log(jsonData); //end!!
                if(jsonData.exito){
                    //showPreloader('exito', jsonData.msg); //Cambia a mensaxe do preloader
                    deleteRow(tr.id);
                    //setTimeout(function(){closebutton.click();}, 1500); // Pecha Preloader
                    console.log(jsonData); //end!!

                }else {
                    //showPreloader('error', jsonData.msg);
                    // mensaje ...
                    console.log(jsonData); //end!!
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
            	//SOLVENUP_ADMIN.toggleSaveMessage(false);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(jqXHR);
                    //alert( textStatus );
            }
        });
    }

    // Borramos aprobados e rexeitados
    const deleteRow = (row_id)=>{
        console.log(row_id);
        const row2delete = table_message_body.querySelector('#' + row_id);
        console.log(row2delete);
        row2delete.remove();
        comprobaTabla();

    }

    // Ocultar tabla si  jon hai filas
    const comprobaTabla = ()=>{
        console.log(table_message_body.querySelectorAll(' tr').length);
        if(!table_message_body.querySelectorAll('tr').length){
            noMessagesDiv.classList.add('is-visible');
            table_message_body.parentNode.style.display='none';
        }
    }




    const filterModeration = (status)=>{
        const url = new URL(location.href);
        console.log(url);
        console.log('status: '+ status);
        const search_params = url.searchParams;
        search_params.set('status_id', status);
        url.search = search_params.toString();

        const newurl = url.toString();
        location.assign(newurl);
    }




    const displayMessage = (tr)=>{

        const message = tr.dataset.message;
        const mydiv = preview.querySelector('.mensaje-preview');
        mydiv.innerHTML=message;
        closebutton.style.display='block';
        preview.classList.remove('sjb-hidden')
        //console.log('Vista previa mensaje ' + id);
    }






    /*

         _       _         _
        | |     (_)  ___  | |_    ___   _ __     ___   _ __   ___
        | |     | | / __| | __|  / _ \ | '_ \   / _ \ | '__| / __|
        | |___  | | \__ \ | |_  |  __/ | | | | |  __/ | |    \__ \
        |_____| |_| |___/  \__|  \___| |_| |_|  \___| |_|    |___/
    */

    if(table_message_body)
    table_message_body.addEventListener('click', function(e) {
        // Simulamos eventos on de jQuery incluso para elementos live!
        if(e.target && e.target.classList.contains('read')) {
            // boton leer
            console.log("leer mensaxe (e contestar se queres");
            let tr =e.target.parentElement.parentElement.parentElement;
            let id = tr.dataset.id; // id mensaje
            console.log(id);
            displayMessage(tr);
            //location.assign("http://www.mozilla.org");

        }else if(e.target && e.target.classList.contains('approve')) {
            console.log("Aprobar");
            let tr = e.target.parentElement.parentElement.parentElement;
            //let id = tr.id;  deleteRow(tr.dataset.id);
            console.log(tr);
            cambiarStatusMensaje(tr, 1);


        }else if(e.target && e.target.classList.contains('reject')) {
            // boton leer
            console.log("contestar reject");
            let tr =e.target.parentElement.parentElement.parentElement;
            // let id = tr.id;
            // console.log(id);
            cambiarStatusMensaje(tr, 2);        }
    });

    if(moderationLinks)
    moderationLinks.addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('filter-moderation')) {
            //let id = tr.id;  deleteRow(tr.dataset.id);
            let tr =e.target.parentElement.parentElement.parentElement;
            // console.log(e.target);
            // console.log(e.target.dataset.status);
            filterModeration(e.target.dataset.status);

        }
    });

	if(closebutton)
    closebutton.addEventListener('click', function(e) {
        const mydiv = preview.querySelector('.mensaje-preview');
        preview.classList.add('sjb-hidden')
        mydiv.innerHTML = '';

    });
    console.log('ok, sjbboard moderation.js cargado');



})();

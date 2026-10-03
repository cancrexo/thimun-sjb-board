


(() => {
    'use strict';


    const DESTINATARIOS_ARR = [];     // array cos id dos destinatarios seleccionados

    // identificadores HTML
	const
    submit_message      = document.querySelector('#sjbboard-send'),
    showcontacts 		= document.querySelector('#sjbboard-showcontacts'),
    addressBook         = document.querySelector('#sjbboard-addressbook > .content'),
    buttonadd           = document.querySelector('#addcontact'),
    destinatarios       = document.querySelector('.destinatarios-box >ul'),
    asunto              = document.querySelector('#sjbboard-subject'),
    mensaxe             = document.querySelector('#sjbboard-message'),
    ajax_loader			= document.querySelector('#carga-externa'),
    closebutton         = document.querySelector('#sjbboard .closebutton'),
    nada = null;


    let CONTACTOS_CARGADOS            = [];    // array cos contactos cargados

    /*
     _____                          _
    |  ___|  _   _   _ __     ___  (_)   ___    _ __    ___
    | |_    | | | | | '_ \   / __| | |  / _ \  | '_ \  / __|
    |  _|   | |_| | | | | | | (__  | | | (_) | | | | | \__ \
    |_|      \__,_| |_| |_|  \___| |_|  \___/  |_| |_| |___/

    */

    const resetPreloader = () => {
        ajax_loader.classList.add('sjb-hidden');
        ajax_loader.classList.remove('procesando', 'exito','error');
        ajax_loader.querySelector('#sjbboard-ajax-msg').innerText = '';
    }

    const showPreloader = (myclass, msg = 'Msg') => {
        ajax_loader.classList.remove('sjb-hidden', 'procesando', 'exito','error');
        ajax_loader.querySelector('#sjbboard-ajax-msg').innerText = msg;
        ajax_loader.classList.add(myclass);
    }

    // Resaltar erros
    const SJBresaltaCampoBind =  ( campo ) =>{

		campo.classList.add( 'errorBg' );
		campo.addEventListener( 'click' , () => {
			campo.classList.remove( 'errorBg' );
		} );
    };


    // Borrar contacto
    const borraRecipient = (target) => {

        console.log( target.dataset.id );

        // Quita de pantalla
        const borrar = document.getElementById(target.id);
        borrar.parentNode.removeChild(borrar);

        // Quita do array
        for( let i = 0; i < DESTINATARIOS_ARR.length; i++){
            console.log(i + ' ==> ' + target.dataset.id);
            console.log(' Valor ==> ' + DESTINATARIOS_ARR[i]);
            if ( DESTINATARIOS_ARR[i] === target.dataset.id){
                console.log('Quitando:');
                DESTINATARIOS_ARR.splice(i, 1);
                break;
            }
        }

    }

    const borraForm = () =>{
        destinatarios.innerHTML='';
        asunto.value ='';
        mensaxe.value ='';
        DESTINATARIOS_ARR.splice(0, DESTINATARIOS_ARR.length);
    }


    // Recuperar contactos que haxa na lista para saber cales para enviar mensaxe
    const getRecipients = () =>{
        let listacontactos = destinatarios.querySelectorAll('li');
        const recipients = [];
        for(let L of listacontactos){
            // console.log(L);
            // console.log(L.dataset.idrecipient);
            recipients.push(L.dataset.idrecipient);
        }
        return recipients;
    }

    // Valida mensaje (1 destinatario, asunto y msg validos)
    const comprobar = () =>{
        let listacontactos = destinatarios.querySelectorAll('li');
        //console.log(listacontactos.length);

        if(!listacontactos.length)
            SJBresaltaCampoBind(destinatarios);
        else if(!asunto.value.length && !window.confirm('Send without Subject') )
            SJBresaltaCampoBind(asunto);
        else if(!mensaxe.value.length)
            SJBresaltaCampoBind(mensaxe);
		else return true;
		return false;
	}


    // Envia endispois de validar
    const Send = () =>{

        resetPreloader();

        if(!comprobar())
            return false;


        let data2Send = {
            action          : SJB_BOARD.ajax_action, //'topotamadre', //WP action
            sjb_noncename   : SJB_BOARD.ajax_nonce,
            quefasemos      : 'newmessage',
            resposta        : false,
            recipients      : DESTINATARIOS_ARR,
            asunto          : asunto.value,
            mensaxe         : mensaxe.value,
        }

        console.log(data2Send);

        showPreloader();

        jQuery.ajax({
            url: SJB_BOARD.ajaxurl ,
            dataType: "json",
            type: 'post',
            cache: false,
            data: data2Send,

            success: function ( jsonData ) {
                console.log(jsonData); //end!!
                if(jsonData.exito){
                    showPreloader('exito', jsonData.msg); //Cambia a mensaxe do preloader
                    borraForm();
                    setTimeout(function(){closebutton.click();}, 1500); // Pecha Preloader
                    console.log(jsonData); //end!!

                }else {
                    showPreloader('error', jsonData.msg);
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

    // lee os contactos (TODOS aso que se lle permie escribir) desde a bbdd
    const getContacts = () =>{

        resetPreloader();

        let data2Send = {
            action          : SJB_BOARD.ajax_action,
            sjb_noncename   : SJB_BOARD.ajax_nonce,
            quefasemos      : 'getcontacts',
            resposta        : false,
        }

        console.log(data2Send);

        showPreloader();

        jQuery.ajax({
            url: SJB_BOARD.ajaxurl ,
            dataType: "json",
            type: 'post',
            cache: false,
            data: data2Send,
            success: function ( jsonData ) {
                if(jsonData.exito){
                    //console.log(jsonData.datos); //end!!
                    contactsDisplay(jsonData);
                }else {
                    showPreloader('error', jsonData.msg);
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

    // Amosa a libreta de direcsións,
    const contactsDisplay = (jsonData)=>{
        // Filtras spara eliminar os que xa están engadidos
        CONTACTOS_CARGADOS = contactsFilter(jsonData.datos); // Object!
        // Maquetas a libreta de dierccións
        prepareAddresBook();
        addressBook.parentElement.classList.remove('hidden');
        resetPreloader();
    }

    // Elimina das direccions cargadasda BBDD, os que xa están en DESTINATARIOS_ARR
    const contactsFilter = (contacts) =>{
        const tmp = new Array();
        Object.entries(contacts).forEach(C => {
            if(DESTINATARIOS_ARR.indexOf(C[1]['ID']) == -1){
                tmp.push( {campoValor:C[1]['ID'],  campoTexto: C[1]['display_name'],  checked:0 });

            }else console.log(C[1]['ID'] + ' naa');
          })
        console.log (typeof tmp);
        return tmp;
    }


    // Lee os cehckboxes marcados  e asignaos ao array de contactos. Modifica a represntacion visual dos destinatarios
    const addNewContacts = ()=>{

        const SELECCIONADOS = dameCheckBoxes({nomeGrupo:'sjbboard', dameTextos:1});
        console.log('SELECCIONADOS');
        console.log(SELECCIONADOS.valores);

        // Añadir sleccionado ao array de destinatarios
        // e de paso printalos en pantalla
        // console.log('DESTINATARIOS_ARR');
        // console.log(DESTINATARIOS_ARR);
        SELECCIONADOS.valores.forEach(element =>{

            // Añade destinatario ao array
            DESTINATARIOS_ARR.push(element.id);

            // printa -->crear ul e metelo
            //    const patron = '<li  class="recipient" id="li-user-{USERID}"><span><i class="fas fa-user-times delete-recipient" title="Remove recipient"></i><em>{USERNAME}}</em></span></li>';
            let li =  document.createElement('li');
                li.classList.add('recipient');
                li.setAttribute('id','li-user-' + element.id );
                li.dataset.id=element.id;

            let span = document.createElement('span');
                span.innerHTML= '<i class="fas fa-user-times delete-recipient" title="Remove recipient"></i><em>'+element.texto+'</em>'

            li.append(span);

            // Sacao por pantalla
            destinatarios.append(li);

            console.log(element.id);
            console.log(element.texto)

        });
        console.log('DESTINATARIOS_ARR');
        console.log(DESTINATARIOS_ARR);

    }

    const prepareAddresBook = () =>{
        // Amosala
        const opsions = SJBScrollableCheckBoxes({capa:'topotamadre',  nomeGrupo:'sjbboard',  arrValores: CONTACTOS_CARGADOS}); //labels with ceheckbxoes
        addressBook.innerHTML=opsions;
    }



    /*

         _       _         _
        | |     (_)  ___  | |_    ___   _ __     ___   _ __   ___
        | |     | | / __| | __|  / _ \ | '_ \   / _ \ | '__| / __|
        | |___  | | \__ \ | |_  |  __/ | | | | |  __/ | |    \__ \
        |_____| |_| |___/  \__|  \___| |_| |_|  \___| |_|    |___/
    */
    if(showcontacts)
	showcontacts.addEventListener('click', () => {
        getContacts();
    });



    if(destinatarios){
        console.log(destinatarios);
        destinatarios.addEventListener('click', function(e) {
            // Simulamos eventos on de jQuery incluso para elementos live!
            if(e.target && e.target.classList.contains('delete-recipient')) {
                borraRecipient(e.target.parentElement.parentElement); // Click nun  <li><span><i>
                console.log("delete-recipient");
            }
        });
    }

    if(submit_message)
	submit_message.addEventListener('click', () => {
        Send();
    });


    // Pecha janela de preloader cando hai erros
    if(closebutton)
    closebutton.addEventListener('click', function(e) {
        // Simulamos eventos on de jQuery incluso para elem
        resetPreloader();
    });


    // Boton añadir contactos marcados
    buttonadd.addEventListener('click', function(e) {
        if(addressBook.parentElement.classList.contains('hidden'))
            return;
        addNewContacts();
        // Ocultar  lista
        addressBook.parentElement.classList.add('hidden');
    });



    resetPreloader();

    console.log('ok, sjbboard compose.js cargado');



})();

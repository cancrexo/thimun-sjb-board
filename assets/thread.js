(() => {

    'use strict';

	// identificadores HTML
    const
    replywrapper        = document.querySelector('#reply-messsage-wrapper'),
    replyButton         = document.querySelector('#sjbboard-reply'),
    cancelreplyButton   = document.querySelector('#sjbboard-cancelreply'),
    sendReplyButton     = document.querySelector('#sjbboard-sendreply'),

    msg_id              = document.querySelector('#msg_id'),
    asunto              = document.querySelector('#sjbboard-subject'),
    mensaxe             = document.querySelector('#sjbboard-message'),
    ajax_loader			= document.querySelector('#carga-externa'),
    closebutton         = document.querySelector('#sjbboard .closebutton');


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


    const enable_reply = ()=>{
        console.log('reply');
        replywrapper.classList.add('is-visible');
        replyButton.classList.remove('is-visible');
    }
    const disable_reply = ()=>{
        console.log('disable reply');
        replywrapper.classList.remove('is-visible');
        replyButton.classList.add('is-visible');
    }



    // Valida mensaje (1 destinatario, asunto y msg validos)
    const comprobar = () =>{

        if(!asunto.value.length)
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
            quefasemos      : 'reply',
            resposta        : 1,
            msg_id          : msg_id.value,
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
                    //https://thimun-online.org/sjb-message-board/
                    setTimeout(function(){closebutton.click();
                        location.assign('https://thimun-online.org/sjb-message-board/');
                     }, 1500); // Pecha Preloader
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

    /*

         _       _         _
        | |     (_)  ___  | |_    ___   _ __     ___   _ __   ___
        | |     | | / __| | __|  / _ \ | '_ \   / _ \ | '__| / __|
        | |___  | | \__ \ | |_  |  __/ | | | | |  __/ | |    \__ \
        |_____| |_| |___/  \__|  \___| |_| |_|  \___| |_|    |___/
    */

    //if(replyButton)
    replyButton.addEventListener('click', () => {
        enable_reply();
    });


    //if(sendReplyButton)
    sendReplyButton.addEventListener('click', () => {
        Send();
    });

    cancelreplyButton.addEventListener('click', () => {
        disable_reply();
    });
    console.log('ok, sjbboard thread.js cargado');



})();

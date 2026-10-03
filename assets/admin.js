

(() => {
	'use strict';

    const
        btnUpdate  	        = document.querySelector('.update-sjb-form-options'),
        moderation  	    = document.querySelectorAll('input[name="moderation"]'),
        in_footer  	        = document.querySelectorAll('input[name="in_footer"]'),
        delete_onuninstall  = document.querySelectorAll('input[name="delete_onuninstall"]'),
        debug_mode  	    = document.querySelectorAll('input[name="debug_mode"]'),
        div_messages        = document.querySelector('.ajax-save-result');

    btnUpdate.addEventListener('click', () => {
        if(validateForm())
           saveOptionsForm();
	})

    // Devolve valor dun grupo  de checboxes, raiods, etc
    const getchekboxesGroup = ( coleccion )=>{
        for(let C of coleccion){
            if(C.checked) return C.value;
        }
        return -1;
    }
    const validateForm = ()=>{
		console.log('Validated!');
		return true;
	}

    const toggleSaveMessage = (exito = true) =>{
        console.log('Saved ' +  exito );
		let capa = exito ? 'exito' : 'error';
        div_messages.classList.remove('hidden');
        setTimeout(()=>  div_messages.classList.add('hidden'),1000);
    }

	const  saveOptionsForm= ()=>{

       let data2Send = {
            sjb_noncename: SJB_BOARD.ajax_nonce,
            action : SJB_BOARD.ajax_action, //'topotamadre', //WP action
            quefasemos: 'backend',
            myplugindata:{
                // tes un node list, hai que iterar o bucle
                moderation:  parseInt(getchekboxesGroup(moderation), 10),
                in_footer:  parseInt(getchekboxesGroup(in_footer), 10),
                delete_onuninstall:  parseInt(getchekboxesGroup(delete_onuninstall), 10),
            	debug_mode:parseInt(getchekboxesGroup(debug_mode), 10),
            }

        }

        //console.log(data2Send);


        jQuery.ajax({
            url: ajaxurl ,
            dataType: "json",
            type: 'post',
            cache: false,
            data: data2Send,

            success: function ( jsonData ) {
                console.log(jsonData);
                toggleSaveMessage(jsonData.exito);

            },
            error: function(jqXHR, textStatus, errorThrown){
            	toggleSaveMessage(false);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(jqXHR);
            }
        })
    }



})();
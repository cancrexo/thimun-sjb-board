(() => {

    'use strict';

	// identificadores HTML
	const
		table_messages = document.querySelector('#message-board');




    /*
     _____                          _
    |  ___|  _   _   _ __     ___  (_)   ___    _ __    ___
    | |_    | | | | | '_ \   / __| | |  / _ \  | '_ \  / __|
    |  _|   | |_| | | | | | | (__  | | | (_) | | | | | \__ \
    |_|      \__,_| |_| |_|  \___| |_|  \___/  |_| |_| |___/

    */







    /*

         _       _         _
        | |     (_)  ___  | |_    ___   _ __     ___   _ __   ___
        | |     | | / __| | __|  / _ \ | '_ \   / _ \ | '__| / __|
        | |___  | | \__ \ | |_  |  __/ | | | | |  __/ | |    \__ \
        |_____| |_| |___/  \__|  \___| |_| |_|  \___| |_|    |___/
    */

   if(table_messages)
   table_messages.addEventListener('click', function(e) {
       // Simulamos eventos on de jQuery incluso para elementos live!
       if(e.target && e.target.classList.contains('read')) {
           // boton leer
           console.log("leer mensaxe (e contestar se queres");
           let tr =e.target.parentElement.parentElement;
           let id = tr.dataset.id;
           console.log(id);
           location.assign("http://www.mozilla.org"); // o

       }else   if(e.target && e.target.classList.contains('reply')) {
        // boton leer
        console.log("contestar mensaxe");
    }
   });

    console.log('ok, sjbboard board.js cargado');



})();

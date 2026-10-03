
	/***********************************************************************************************************************
	 *	Crea unha serie de checkboxes que se poden empregar para substituir un select multiple por exemplo
	 *  Resibe Obj:
	 *		capa	-->nome da capa ( div, p, etc ) onde meter os checkboxes
	 *		nome-->nome do grupo de checkboxes
	 *		arrValores -> Array de obxectos da forma {campoValor:nnnn, campoTexto:'texto do seletc', checked:0|1}}
	 *@void
	*
	***********************************************************************************************************************/
	SJBScrollableCheckBoxes = ( parametros ) =>{

		//var capa 			= jQuery( parametros.capa );
		let nomeGrupo 	= parametros.nomeGrupo;
        let arrValores 	= parametros.arrValores;  // Array de obxectos
        let nAltura;
		if( !parametros.conOverflow )
		nAltura		= parametros.altura ? parametros. altura  : 120;
        let marcar, marcarStr, opsions = '';

		if( typeof arrValores != 'undefined' && arrValores != null ){
			for ( j = 0; j < arrValores.length; j++ ){
				marcar = typeof arrValores[j].checked  != 'undefined' ? parseInt( arrValores[j].checked, 10 ) : 0;
				marcarStr = marcar ? 'checked="checked"' : '';
				opsions +=  '<label class="SJB_labelCheckbox"><input ' + marcarStr  + ' type="checkbox" name="'+nomeGrupo+'[]" value="' +arrValores[j].campoValor + '"/>' +arrValores[j].campoTexto + '</label>';
			}
        }
        return opsions; // String
		// }else {
		// 	// Poñemos o campo default si se definiu ou poño un xenerico
		// 	jQuery( parametros.capa ).removeClass( 'SJB_checkboxesON' ).addClass( 'SJB_checkboxesOFF' );
		// 	jQuery( parametros.capa ).html( typeof !isUndefined( parametros.defaultText ) ? parametros.defaultText : 'Seleccione...' );
		// }
		// if( !parametros.conOverflow )
		//  jQuery( parametros.capa ).height( nAltura );
	};

	/***********************************************************************************************************************
	 	Marca todolos checkboxes dun grupo

	***********************************************************************************************************************/
	checkAllCheckBoxes = ( nomeGrupo )=>{

		jQuery( "input[name='"+nomeGrupo+"[]']" ).each( function( k ){
			if( !jQuery( this ).attr( "checked" ) ) jQuery( this ).attr( "checked", true );
		} );
		jQuery( "input[name='"+nomeGrupo+"[]']" ).first( ).attr( "checked", true );
	};




	/***********************************************************************************************************************
		A partir de un grupo de checkboxes, devolve  obxecto con:
		Nº de checkboxes seleccionados
		Un array cos valores dos checkboxes seleccionados
		Si adicionalmente recibes dameTextos:1 devolvense tamén os textos dos checkboxes

		@Obxecto
	*
	***********************************************************************************************************************/
	dameCheckBoxes = ( parametros ) =>{
		let nomeGrupo	= parametros.nomeGrupo;
		let resultado = new Array( );
		let totalMarcados = 0;
		jQuery.each( jQuery( "input[name='" + nomeGrupo + "[]']:checked" ), function( ) {
					totalMarcados ++;
					if( parametros.dameTextos )resultado.push( {id:jQuery( this ).val( ), texto: jQuery( this ).parent( ).text( ) } );
					else resultado.push( jQuery( this ).val( ) );
				} );
		return {total:totalMarcados, valores:resultado};
	};

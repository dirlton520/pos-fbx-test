// jQuery Bootstrap Touch Keyboard plugin
// By Matthew Dawkins
(function($){
    $.fn.keyboard=function(options){
    	// Settings
      var settings=$.extend({
      keyboardLayout:[
          [['q','Q','1'],['w','W', '2'],['e','E','3'],['r','R','4'],['t','T','5'],['y','Y','6'],['u','U','7'],['i','I','8'],['o','O','9'],['p','P','0']],
          [['a','A','-'],['s','S','_'],['d','D','`'],['f','F','@'],['g','G','#'],['h','H',','],['j','J','.'],['k','K','?'],['l','L','=']],
          [['shift','shift','shift'],['z','Z','+'],['x','X',':'],['c','C',';'],['v','V','~'],['b','B','!'],['n','N','$'],['m','M','*'],['del','del','del']],
          [['?123', '?123', 'abc'],['space','space','space'],['enter','enter','enter']]    
      ],   
			numpadLayout:[
				[['7'],['8'],['9'], ['%']],
				[['4'],['5'],['6'], ['+']],
				[['1'],['2'],['3'], ['-']],
				[['del'],['0'],['.'], ['clr']],
				[['enter']]
			],
			telLayout:[
				[['1'],['2'],['3']],
				[['4'],['5'],['6']],
				[['7'],['8'],['9']],
				[['del'],['0'],['.']]
			],
			layout:false,
			type:false,
            btnTpl:'<button type="button">',
            btnClassesMobile:'no-hover',
						btnClasses:'btn btn-default',
            btnActiveClasses:'active btn-primary',
			initCaps:false,
            placement:'bottom',
            trigger:'manual',
			before: false,
			after: false
    },options);
		if (!settings.layout) {
			if (($(this).attr('type')==='tel' && $(this).hasClass('keyboard-numpad')) || settings.type==='numpad') {
				settings.layout=settings.numpadLayout;
			}
			else if (($(this).attr('type')==='tel') || settings.type==='tel') {
				settings.layout=settings.telLayout;
			}
			else {
				settings.layout=settings.keyboardLayout;
			}
		}
		
		if (IS_MOBILE)
		{
			//Make input readonly on mobile
			$(this).attr('readonly','readonly');
		}
    // Keep track of shift status
    var keyboardShift=false;
		var keyboardNum = false;
    // Listen for keypresses
		$(document).off('touchstart mousedown','.jqbtk-row .btn');
    $(document).on('touchstart mousedown','.jqbtk-row .btn', function(e){
        e.preventDefault();
        $(this).addClass(settings.btnActiveClasses);
        
        var keyContent = $(this).attr('data-value');
        if (keyboardNum)
        {
       	 	keyContent=$(this).attr('data-value-alt-2');                       
        }
        else if (keyboardShift)
        {
       	 	keyContent=$(this).attr('data-value-alt');                       
        }
        var parent=$('[aria-describedby='+$(this).closest('.popover').attr('id')+']');
				//emulate event for other plugins
				parent.trigger('keydown');
	
        var currentContent = parent.val();
        switch(keyContent) {
            case 'space':
							//delete any selected text
							deleteSelectedText(parent[0]);
							currentContent = parent.val();
              currentContent+=' ';
              break;
            case 'shift':
              keyboardShift=!keyboardShift;
              keyboardShiftify();
              break;
            case '?123':
            case 'abc':
              keyboardNum=!keyboardNum;
              keyboardNumly();
              break;
            case 'del':
							//delete any selected text
							deleteSelectedText(parent[0]);
							currentContent = parent.val();
              currentContent=currentContent.substr(0,currentContent.length-1);
              break;
            case 'clr':
							//delete any selected text
							deleteSelectedText(parent[0]);
							currentContent = parent.val();
              currentContent='';
              break;
            case 'enter':
							var e = jQuery.Event("keypress");
		 					e.which = 13;
		 					e.keyCode = 13;
		 					parent.trigger(e);
							if(parent.is("textarea"))
							{
								currentContent+='\n';
							}
				
             	break;
            default:
							//delete any selected text
							deleteSelectedText(parent[0]);
							currentContent = parent.val();
              currentContent+=keyContent;
              keyboardShift = false;
        }
        parent.val(currentContent);
        keyboardShiftify();
        keyboardNumly();
    });
      
			$(document).off('touchend mouseup','.jqbtk-row .btn');
	    $(document).on('touchend mouseup','.jqbtk-row .btn',function(){
         $(this).removeClass(settings.btnActiveClasses);
      });
      
			// Prevent clicks on the popover from cancelling the focus
      $(document).off('touchstart mousedown','.jqbtk-row');
	    $(document).on('touchstart mousedown','.jqbtk-row',function(e){
      	e.preventDefault();
      });
		
		var deleteSelectedText = function(textbox) 
		{
			var activeElement = document.activeElement;
			
		   if(activeElement && (activeElement.tagName.toLowerCase() == "textarea" || (activeElement.tagName.toLowerCase() == "input" && activeElement.type.toLowerCase() == "text")) &&
		      activeElement === textbox) 
		   {
		      var startIndex = textbox.selectionStart;
		      var endIndex = textbox.selectionEnd;

		      if(endIndex - startIndex > 0)
		      {
		          var text = textbox.value;
				  var textSelectionRemoved = text.substring(0, textbox.selectionStart) + text.substring(textbox.selectionEnd, text.length);
				  textbox.value = textSelectionRemoved;
				  return true;
		      }
		   }
		   
		   return false;
		}

        // Update keys according to shift status
        var keyboardShiftify=function() {
            $('.jqbtk-container .btn').each(function(){
                switch($(this).attr('data-value')) {
                    case 'shift':
                    case 'del':
                    case 'space':
                    case 'enter':
                        break;
                    default:
                        $(this).text($(this).attr('data-value'+(keyboardShift?'-alt':'')));
                }
            });
        }
        
        keyboardNumly= function() {
            $('.jqbtk-container .btn').each(function(){
                switch($(this).attr('data-value')) {
                    case 'shift':
                    case 'del':
                    case 'space':
                    case 'enter':
                        break;
                    default: 												$(this).text($(this).attr('data-value'+(keyboardNum?'-alt-2':($(this).attr('data-value'+(keyboardShift?'-alt':''))))));
                }
            });
        }

        // Set up a popover on each of the targeted elements
        return this.each(function(){
            $(this).popover({
                content:function() {
                    // Optionally set initial caps
                    if (settings.initCaps && $(this).val().length===0) {
                        keyboardShift=true;
                    }
                    // Set up container
                    var content=$('<div class="jqbtk-container">');
                    $.each(settings.layout,function(){
                        var line=this;
                        var lineContent=$('<div class="jqbtk-row">');
                        $.each(line,function(){
													var btn=$(settings.btnTpl);
													btn.addClass(settings.btnClasses);
													
													if (IS_MOBILE)
													{
														btn.addClass(settings.btnClassesMobile);
													}
													
                          btn.attr('data-value', this[0]).attr('data-value-alt', this[1]).attr('data-value-alt-2', this[2]);
                          switch(this[0]) {
                              case 'shift':
                                  btn.addClass('jqbtk-shift').html('<span class="ion-arrow-up-a"></span>');
                                  break;
                              case 'space':
                                  btn.addClass('jqbtk-space').html('&nbsp;');
                                  break;
                              case 'del':
                                  btn.addClass('jqbtk-del').html('<span class="ion-backspace-outline"></span>');
                                  break;
                              case 'enter':
                                  btn.addClass('jqbtk-enter').html('Enter');
                                  break;
                              case '?123':
                                  btn.addClass('jqbtk-abc123').html('?123');
                                  break;
                              case 'abc':
                                  btn.addClass('jqbtk-num').text(btn.attr('data-value'+(keyboardNum?'-alt-2':'')));
                             	 	break;
                              default:
                                  btn.text(btn.attr('data-value'+(keyboardShift?'-alt':'')));
                          }
                          lineContent.append(btn);
                        });
                        content.append(lineContent);
                    });
                    return content;
                },
                html:true,
                placement:settings.placement,
                trigger:settings.trigger,
                container:'#register_container',
                viewport:'#register_container'
            });
			
			//Attach optional event handler function for each event
			if(settings.before)
			{
				$(this).on('show.bs.popover', settings.before);
			}
			if(settings.after)
			{
				$(this).on('hidden.bs.popover', settings.after);
			}
        });
    }
}(jQuery));
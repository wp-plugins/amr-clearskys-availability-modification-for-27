YAHOO.namespace("clearskys.booking");

YAHOO.clearskys.booking.Search = function() {
			return {
				init: function() {
				},
				submit: function(e) {
					var searchcallback = {
						success: function(o) {
								if(o.responseText != '') {
									YAHOO.util.Dom.get('bookingresultpanel').innerHTML = o.responseText;
									YAHOO.clearskys.booking.Menu.init();
								}
								YAHOO.util.Dom.get('busysearch').style.visibility = 'hidden';
								},
						failure: function(o) { alert(o.statusText); }
					};
					YAHOO.util.Event.stopEvent(e);
					YAHOO.util.Dom.get('busysearch').style.visibility = 'visible';
					cpage = window.location.href;
					propertyid = encodeURIComponent(YAHOO.util.Dom.get('propertyid').value);
					sstatus = encodeURIComponent(YAHOO.util.Dom.get('sstatus').value);
					m = encodeURIComponent(YAHOO.util.Dom.get('m').value);
					s = encodeURIComponent(YAHOO.util.Dom.get('s').value);
					cpageuri = 'propertyid=' + propertyid + '&sstatus=' + sstatus + '&m=' + m + '&s=' + s + '&action=ajaxsearch&call=ajax';
					cpageuri += '&nocache=' + new Date().getTime();
					(cpage.indexOf('?') !=-1) ? cpage = cpage + '&' + cpageuri : cpage = cpage + '?' + cpageuri;
					YAHOO.util.Connect.asyncRequest('GET', cpage, searchcallback, null);
				}
			};
		} ();

YAHOO.clearskys.booking.Menu = function() {
	return {
		init: function() {		
				var tid = '';
				var ntrans = '';
				
				var deletecallback = {
					success: function(o) { },
					failure: function(o) { alert(o.statusText);},
					argument: ''
				}
				
				var removebooking = function(cld) {
					panel = document.getElementById('bookingresultpanel');
					achild = document.getElementById(cld);
					panel.removeChild(achild);
				}
				
				var adelete = function(e) {
					YAHOO.util.Event.stopEvent(e);
					if(confirm('Are you sure you want to delete this booking?')) {
						tid = this.id.substr(14,this.id.length-14);
						
						anode = 'booking-'+tid;
						var anim = new YAHOO.util.Anim(anode, { opacity: { to: 0 } }, 1, YAHOO.util.Easing.easeOut);
						anim.onComplete.subscribe(function() { removebooking(anode); } );
						anim.animate();
						cpage = window.location.href;
						cpageuri = 'bookingid=' + tid + '&action=ajaxdelete&call=ajax';
						cpageuri += '&nocache=' + new Date().getTime();
						(cpage.indexOf('?') !=-1) ? cpage += '&' + cpageuri : cpage += '?' + cpageuri;
						YAHOO.util.Connect.asyncRequest('GET', cpage, deletecallback, null);
					}
				}
				
				var elements = YAHOO.util.Dom.getElementsByClassName('deletelink','a','bookingresultpanel');
   				for(n=0;n<elements.length;n++) {
   					tid = elements[n].id.substr(14,elements[n].id.length-14);
   					YAHOO.util.Event.addListener(elements[n].id, "click", adelete);
   				}
			}
			};

} ();

YAHOO.util.Event.addListener(window, "load", YAHOO.clearskys.booking.Menu.init);
YAHOO.util.Event.addListener("bookingsearchform", "submit", YAHOO.clearskys.booking.Search.submit);
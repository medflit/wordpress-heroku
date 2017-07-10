!function(t){"use strict";function e(t,e,s,h,r){this._internal=new i(t,e,s,h,r)}function i(t,e,i,s,h){this.onStartCallback=e,this.onProgressCallback=i,this.onFinishCallback=s,this.thisArg=h,this.xhr=t,this.state=0,this.charOffset=0,this.offset=0,this.url="",this.withCredentials=!1,this.timeout=0}function s(){this._data={}}function h(){this._listeners=new s}function r(t){l(function(){throw t},0)}function n(t){this.type=t,this.target=void 0}function o(t,e){n.call(this,t),this.data=e.data,this.lastEventId=e.lastEventId}function a(t,e){h.call(this),this.onopen=void 0,this.onmessage=void 0,this.onerror=void 0,this.url="",this.readyState=g,this.withCredentials=!1,this._internal=new u(this,t,e)}function u(t,i,s){this.url=i.toString(),this.readyState=g,this.withCredentials=y&&void 0!=s&&Boolean(s.withCredentials),this.es=t,this.initialRetry=_(1e3,0),this.heartbeatTimeout=_(45e3,0),this.lastEventId="",this.retry=this.initialRetry,this.wasActivity=!1;var h=void 0!=s&&void 0!=s.Transport?s.Transport:m,r=new h;this.transport=new e(r,this.onStart,this.onProgress,this.onFinish,this),this.timeout=0,this.currentState=S,this.dataBuffer=[],this.lastEventIdBuffer="",this.eventTypeBuffer="",this.state=T,this.fieldStart=0,this.valueStart=0,this.es.url=this.url,this.es.readyState=this.readyState,this.es.withCredentials=this.withCredentials,this.onTimeout()}function c(){this.CONNECTING=g,this.OPEN=C,this.CLOSED=x}var l=t.setTimeout,d=t.clearTimeout,f=function(){};e.prototype.open=function(t,e){this._internal.open(t,e)},e.prototype.cancel=function(){this._internal.cancel()},i.prototype.onStart=function(){if(1===this.state){this.state=2;var t=0,e="",i=void 0;if("contentType"in this.xhr)t=200,e="OK",i=this.xhr.contentType;else try{t=this.xhr.status,e=this.xhr.statusText,i=this.xhr.getResponseHeader("Content-Type")}catch(s){t=0,e="",i=void 0}void 0==i&&(i=""),this.onStartCallback.call(this.thisArg,t,e,i)}},i.prototype.onProgress=function(){if(this.onStart(),2===this.state||3===this.state){this.state=3;var t="";try{t=this.xhr.responseText}catch(e){}for(var i=this.charOffset,s=t.length,h=this.offset;h<s;h+=1){var r=t.charCodeAt(h);r!=="\n".charCodeAt(0)&&r!=="\r".charCodeAt(0)||(this.charOffset=h+1)}this.offset=s;var n=t.slice(i,this.charOffset);this.onProgressCallback.call(this.thisArg,n)}},i.prototype.onFinish=function(){this.onProgress(),3===this.state&&(this.state=4,0!==this.timeout&&(d(this.timeout),this.timeout=0),this.onFinishCallback.call(this.thisArg))},i.prototype.onReadyStateChange=function(){void 0!=this.xhr&&(4===this.xhr.readyState?0===this.xhr.status?this.onFinish():this.onFinish():3===this.xhr.readyState?this.onProgress():2===this.xhr.readyState)},i.prototype.onTimeout2=function(){this.timeout=0;var e=/^data\:([^,]*?)(base64)?,([\S]*)$/.exec(this.url),i=e[1],s="base64"===e[2]?t.atob(e[3]):decodeURIComponent(e[3]);1===this.state&&(this.state=2,this.onStartCallback.call(this.thisArg,200,"OK",i)),2!==this.state&&3!==this.state||(this.state=3,this.onProgressCallback.call(this.thisArg,s)),3===this.state&&(this.state=4,this.onFinishCallback.call(this.thisArg))},i.prototype.onTimeout1=function(){this.timeout=0,this.open(this.url,this.withCredentials)},i.prototype.onTimeout0=function(){var t=this;this.timeout=l(function(){t.onTimeout0()},500),3===this.xhr.readyState&&this.onProgress()},i.prototype.handleEvent=function(t){"load"===t.type?this.onFinish():"error"===t.type?this.onFinish():"abort"===t.type?this.onFinish():"progress"===t.type?this.onProgress():"readystatechange"===t.type&&this.onReadyStateChange()},i.prototype.open=function(e,i){0!==this.timeout&&(d(this.timeout),this.timeout=0),this.url=e,this.withCredentials=i,this.state=1,this.charOffset=0,this.offset=0;var s=this,h=/^data\:([^,]*?)(?:;base64)?,[\S]*$/.exec(e);if(void 0!=h)return void(this.timeout=l(function(){s.onTimeout2()},0));if((!("ontimeout"in this.xhr)||"sendAsBinary"in this.xhr||"mozAnon"in this.xhr)&&void 0!=t.document&&void 0!=t.document.readyState&&"complete"!==t.document.readyState)return void(this.timeout=l(function(){s.onTimeout1()},4));this.xhr.onload=function(t){s.handleEvent({type:"load"})},this.xhr.onerror=function(){s.handleEvent({type:"error"})},this.xhr.onabort=function(){s.handleEvent({type:"abort"})},this.xhr.onprogress=function(){s.handleEvent({type:"progress"})},this.xhr.onreadystatechange=function(){s.handleEvent({type:"readystatechange"})},this.xhr.open("GET",e,!0),this.xhr.withCredentials=i,this.xhr.responseType="text","setRequestHeader"in this.xhr&&this.xhr.setRequestHeader("Accept","text/event-stream");try{this.xhr.send(void 0)}catch(r){throw r}"readyState"in this.xhr&&void 0!=t.opera&&(this.timeout=l(function(){s.onTimeout0()},0))},i.prototype.cancel=function(){0!==this.state&&4!==this.state&&(this.state=4,this.xhr.onload=f,this.xhr.onerror=f,this.xhr.onabort=f,this.xhr.onprogress=f,this.xhr.onreadystatechange=f,this.xhr.abort(),0!==this.timeout&&(d(this.timeout),this.timeout=0),this.onFinishCallback.call(this.thisArg)),this.state=0},s.prototype.get=function(t){return this._data[t+"~"]},s.prototype.set=function(t,e){this._data[t+"~"]=e},s.prototype["delete"]=function(t){delete this._data[t+"~"]},h.prototype.dispatchEvent=function(t){t.target=this;var e=t.type.toString(),i=this._listeners,s=i.get(e);if(void 0!=s)for(var h=s.length,n=void 0,o=0;o<h;o+=1){n=s[o];try{"function"==typeof n.handleEvent?n.handleEvent(t):n.call(this,t)}catch(a){r(a)}}},h.prototype.addEventListener=function(t,e){t=t.toString();var i=this._listeners,s=i.get(t);void 0==s&&(s=[],i.set(t,s));for(var h=s.length;h>=0;h-=1)if(s[h]===e)return;s.push(e)},h.prototype.removeEventListener=function(t,e){t=t.toString();var i=this._listeners,s=i.get(t);if(void 0!=s){for(var h=s.length,r=[],n=0;n<h;n+=1)s[n]!==e&&r.push(s[n]);0===r.length?i["delete"](t):i.set(t,r)}},o.prototype=n.prototype;var p=t.XMLHttpRequest,v=t.XDomainRequest,y=void 0!=p&&void 0!=(new p).withCredentials,m=y||void 0!=p&&void 0==v?p:v,S=-1,g=0,C=1,x=2,E=3,T=4,w=5,b=6,A=7,B=/^text\/event\-stream;?(\s*charset\=utf\-8)?$/i,R=1e3,I=18e6,_=function(t,e){var i=t;return i!==i&&(i=e),i<R?R:i>I?I:i},F=function(t,e,i){try{"function"==typeof e&&e.call(t,i)}catch(s){r(s)}};u.prototype.onStart=function(t,e,i){if(this.currentState===g)if(void 0==i&&(i=""),200===t&&B.test(i)){this.currentState=C,this.wasActivity=!0,this.retry=this.initialRetry,this.readyState=C,this.es.readyState=C;var s=new n("open");this.es.dispatchEvent(s),F(this.es,this.es.onopen,s)}else if(0!==t){var h="";h=200!==t?"EventSource's response has a status "+t+" "+e.replace(/\s+/g," ")+" that is not 200. Aborting the connection.":"EventSource's response has a Content-Type specifying an unsupported type: "+i.replace(/\s+/g," ")+". Aborting the connection.",r(new Error(h)),this.close();var s=new n("error");this.es.dispatchEvent(s),F(this.es,this.es.onerror,s)}},u.prototype.onProgress=function(t){if(this.currentState===C){var e=t.length;0!==e&&(this.wasActivity=!0);for(var i=0;i<e;i+=1){var s=t.charCodeAt(i);if(this.state===E&&s==="\n".charCodeAt(0))this.state=T;else if(this.state===E&&(this.state=T),s==="\r".charCodeAt(0)||s==="\n".charCodeAt(0)){if(this.state!==T){this.state===w&&(this.valueStart=i+1);var h=t.slice(this.fieldStart,this.valueStart-1),r=t.slice(this.valueStart+(this.valueStart<i&&t.charCodeAt(this.valueStart)===" ".charCodeAt(0)?1:0),i);if("data"===h)this.dataBuffer.push(r);else if("id"===h)this.lastEventIdBuffer=r;else if("event"===h)this.eventTypeBuffer=r;else if("retry"===h)this.initialRetry=_(Number(r),this.initialRetry),this.retry=this.initialRetry;else if("heartbeatTimeout"===h&&(this.heartbeatTimeout=_(Number(r),this.heartbeatTimeout),0!==this.timeout)){d(this.timeout);var n=this;this.timeout=l(function(){n.onTimeout()},this.heartbeatTimeout)}}if(this.state===T){if(0!==this.dataBuffer.length){this.lastEventId=this.lastEventIdBuffer,""===this.eventTypeBuffer&&(this.eventTypeBuffer="message");var a=new o(this.eventTypeBuffer,{data:this.dataBuffer.join("\n"),lastEventId:this.lastEventIdBuffer});if(this.es.dispatchEvent(a),"message"===this.eventTypeBuffer&&F(this.es,this.es.onmessage,a),this.currentState===x)return}this.dataBuffer.length=0,this.eventTypeBuffer=""}this.state=s==="\r".charCodeAt(0)?E:T}else this.state===T&&(this.fieldStart=i,this.state=w),this.state===w?s===":".charCodeAt(0)&&(this.valueStart=i+1,this.state=b):this.state===b&&(this.state=A)}}},u.prototype.onFinish=function(){if(this.currentState===C||this.currentState===g){this.currentState=S,0!==this.timeout&&(d(this.timeout),this.timeout=0),this.retry>16*this.initialRetry&&(this.retry=16*this.initialRetry),this.retry>I&&(this.retry=I);var t=this;this.timeout=l(function(){t.onTimeout()},this.retry),this.retry=2*this.retry+1,this.readyState=g,this.es.readyState=g;var e=new n("error");this.es.dispatchEvent(e),F(this.es,this.es.onerror,e)}},u.prototype.onTimeout=function(){if(this.timeout=0,this.currentState===S){this.wasActivity=!1;var t=this;this.timeout=l(function(){t.onTimeout()},this.heartbeatTimeout),this.currentState=g,this.dataBuffer.length=0,this.eventTypeBuffer="",this.lastEventIdBuffer=this.lastEventId,this.fieldStart=0,this.valueStart=0,this.state=T;var e=this.url.slice(0,5);e="data:"!==e&&"blob:"!==e?this.url+((this.url.indexOf("?",0)===-1?"?":"&")+"lastEventId="+encodeURIComponent(this.lastEventId)+"&r="+(Math.random()+1).toString().slice(2)):this.url;try{this.transport.open(e,this.withCredentials)}catch(i){throw this.close(),i}}else if(this.wasActivity){this.wasActivity=!1;var t=this;this.timeout=l(function(){t.onTimeout()},this.heartbeatTimeout)}else r(new Error("No activity within "+this.heartbeatTimeout+" milliseconds. Reconnecting.")),this.transport.cancel()},u.prototype.close=function(){this.currentState=x,this.transport.cancel(),0!==this.timeout&&(d(this.timeout),this.timeout=0),this.readyState=x,this.es.readyState=x},c.prototype=h.prototype,a.prototype=new c,a.prototype.close=function(){this._internal.close()},c.call(a),y&&(a.prototype.withCredentials=void 0);var O=function(){return void 0!=t.EventSource&&"withCredentials"in t.EventSource.prototype};void 0!=m&&(void 0==t.EventSource||y&&!O())&&(t.NativeEventSource=t.EventSource,t.EventSource=a)}("undefined"!=typeof window?window:this);
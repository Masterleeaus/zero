(function(){
  function $(q){ return document.querySelector(q); }
  function el(tag, attrs, txt){ var e=document.createElement(tag); if(attrs){for(var k in attrs){e.setAttribute(k, attrs[k]);}} if(txt){e.textContent=txt;} return e; }
  var bubble = el('div', {id:'aiconverse-bubble'});
  var panel = el('div', {id:'aiconverse-panel'});
  var header = el('div', {class:'aiconverse-header'}, 'AIConverse');
  var messages = el('div', {class:'aiconverse-messages'});
  var form = el('form', {class:'aiconverse-form'});
  var input = el('input', {type:'text', placeholder:'Type a message…'});
  var btn = el('button', {type:'submit'}, 'Send');
  form.appendChild(input); form.appendChild(btn);
  panel.appendChild(header); panel.appendChild(messages); panel.appendChild(form);
  bubble.textContent='Chat'; document.body.appendChild(bubble); document.body.appendChild(panel);
  var open=false, convId=null;
  bubble.onclick=function(){ open=!open; panel.style.display=open?'block':'none'; };

  form.addEventListener('submit', function(e){
    e.preventDefault();
    var text = input.value.trim(); if(!text) return;
    var me = el('div', {class:'msg me'}); me.textContent=text; messages.appendChild(me); input.value='';
    fetch('/api/titantalk/send', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'include', body:JSON.stringify({message:text, conversation_id:convId}) })
      .then(r=>r.json()).then(function(data){
        convId = data.conversation_id || convId;
        var bot = el('div',{class:'msg bot'}); bot.textContent = (data.reply && data.reply.text) ? data.reply.text : '[no reply]';
        messages.appendChild(bot);
        messages.scrollTop = messages.scrollHeight;
      }).catch(function(){ var err=el('div',{class:'msg bot'},'[error]'); messages.appendChild(err); });
  });

  var style = document.createElement('style'); style.innerHTML = '#aiconverse-bubble{position:fixed;right:20px;bottom:20px;padding:10px 14px;background:#111;color:#fff;border-radius:20px;cursor:pointer;z-index:99998} #aiconverse-panel{display:none;position:fixed;right:20px;bottom:70px;width:320px;height:420px;background:#fff;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:99999;overflow:hidden} .aiconverse-header{padding:10px 12px;background:#111;color:#fff;font-weight:600} .aiconverse-messages{height:320px;overflow:auto;padding:8px;background:#f7f7f7} .aiconverse-form{display:flex;padding:8px;border-top:1px solid #ddd} .aiconverse-form input{flex:1;margin-right:6px;padding:6px} .msg{margin:6px 0;padding:8px 10px;border-radius:8px;max-width:85%} .msg.me{background:#e1f5fe;align-self:flex-end;margin-left:auto} .msg.bot{background:#eceff1;}';
  document.head.appendChild(style);
})();
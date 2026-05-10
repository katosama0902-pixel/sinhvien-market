<?php
/**
 * Chat View — Danh sách hội thoại + Khung chat
 * Layout: main.php
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$me     = $_SESSION['user'] ?? [];
?>
<div class="px-3 py-3" style="height:calc(100vh - 130px)">
  <div class="flex h-full rounded-2xl overflow-hidden shadow-lg" style="background:#f8f9fa;border:1px solid #e8ecf0">

    <!-- Sidebar -->
    <div class="w-80 flex-shrink-0 flex flex-col border-r border-gray-100 bg-white overflow-hidden">
      <div class="px-5 py-4 font-bold text-gray-800 border-b border-gray-100 bg-white">
        <i class="bi bi-chat-dots mr-2 text-primary"></i>Tin nhắn
      </div>
      <div class="flex-1 overflow-y-auto">
        <?php if (empty($conversations)): ?>
          <div class="p-6 text-center text-gray-400 text-sm">
            <i class="bi bi-chat-square-dots text-4xl block mb-2 opacity-40"></i>
            Chưa có cuộc trò chuyện nào.
          </div>
        <?php else: foreach ($conversations as $c):
            $other   = ((int)$c['buyer_id'] === (int)$me['id']) ? $c['seller_name'] : $c['buyer_name'];
            $initial = mb_strtoupper(mb_substr($other, 0, 1));
            $isActive = ($activeConvId == $c['id']);
          ?>
          <a href="<?= $appUrl ?>/chat/show?id=<?= $c['id'] ?>"
             class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 no-underline transition-colors"
             style="color:inherit;<?= $isActive ? 'background:linear-gradient(135deg,rgba(79,70,229,.08),rgba(139,92,246,.08))' : '' ?>"
             onmouseover="if(!this.style.background||this.style.background.indexOf('gradient')===-1) this.style.background='rgba(79,70,229,.04)'"
             onmouseout="this.style.background='<?= $isActive ? 'linear-gradient(135deg,rgba(79,70,229,.08),rgba(139,92,246,.08))' : '' ?>'">
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                 style="background:linear-gradient(135deg,#4f46e5,#8b5cf6)"><?= $initial ?></div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($other, ENT_QUOTES) ?></div>
              <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars(mb_strimwidth($c['last_message'] ?? 'Bắt đầu chat...', 0, 40, '…'), ENT_QUOTES) ?></div>
            </div>
            <?php if ($c['unread_count'] > 0): ?>
              <span class="text-white text-[11px] font-extrabold px-2 py-0.5 rounded-full flex-shrink-0" style="background:#ef4444"><?= $c['unread_count'] ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Chat box -->
    <div class="flex-1 flex flex-col min-w-0">
      <?php if ($activeConv):
        $otherName = ((int)$activeConv['buyer_id'] === (int)$me['id']) ? $activeConv['seller_name'] : $activeConv['buyer_name'];
      ?>
        <!-- Header -->
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100 bg-white flex-shrink-0">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
               style="background:linear-gradient(135deg,#4f46e5,#8b5cf6)"><?= mb_strtoupper(mb_substr($otherName, 0, 1)) ?></div>
          <div>
            <div class="font-bold text-sm text-gray-800"><?= htmlspecialchars($otherName, ENT_QUOTES) ?></div>
            <div class="text-xs text-gray-400">Re: <?= htmlspecialchars($activeConv['product_title'], ENT_QUOTES) ?></div>
          </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-5 flex flex-col gap-3" id="chatMessages">
          <?php if (empty($messages)): ?>
            <div class="flex flex-col items-center justify-center h-full text-gray-300 gap-2">
              <i class="bi bi-chat-heart text-5xl opacity-30"></i>
              <div class="text-sm">Hãy gửi tin nhắn đầu tiên!</div>
            </div>
          <?php else: foreach ($messages as $msg):
              $isMe = (int)$msg['sender_id'] === (int)$me['id'];
            ?>
            <div class="flex flex-col <?= $isMe ? 'items-end' : 'items-start' ?>" id="msg-<?= $msg['id'] ?>">
              <?php if (!$isMe): ?>
                <div class="text-[11px] text-gray-400 mb-1"><?= htmlspecialchars($msg['sender_name'], ENT_QUOTES) ?></div>
              <?php endif; ?>
              <div class="max-w-[68%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed break-words <?= $isMe ? 'text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-100 rounded-bl-sm' ?>"
                   style="<?= $isMe ? 'background:linear-gradient(135deg,#4f46e5,#8b5cf6)' : '' ?>">
                <?php if (($msg['msg_type'] ?? 'text') === 'offer'): ?>
                  <div class="rounded-xl p-3 text-center text-gray-800" style="background:#fffcf2;border:1px solid #ffd43b;min-width:180px">
                    <div class="font-bold text-sm text-yellow-600 mb-1"><i class="bi bi-tag-fill mr-1"></i>Đề nghị trả giá</div>
                    <div class="text-2xl font-black text-red-600 font-mono mb-2"><?= number_format((int)$msg['offer_price']) ?> đ</div>
                    <?php if ($msg['offer_status'] === 'pending'): ?>
                      <?php if ((int)$activeConv['seller_id'] === (int)$me['id']): ?>
                        <div class="flex gap-2" id="offer-act-<?= $msg['id'] ?>">
                          <button onclick="respondOffer(<?= $msg['id'] ?>,'accepted')" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-white bg-green-500 border-0 cursor-pointer">Đồng ý</button>
                          <button onclick="respondOffer(<?= $msg['id'] ?>,'rejected')" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-white bg-red-500 border-0 cursor-pointer">Từ chối</button>
                        </div>
                      <?php else: ?>
                        <div class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-yellow-100 text-yellow-700"><i class="bi bi-hourglass-split"></i>Chờ duyệt</div>
                      <?php endif; ?>
                    <?php else:
                      $acc = $msg['offer_status'] === 'accepted'; ?>
                      <div class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold text-white <?= $acc ? 'bg-green-500' : 'bg-gray-400' ?>">
                        <i class="bi bi-<?= $acc ? 'check-circle-fill' : 'x-circle-fill' ?>"></i><?= $acc ? 'Đã chốt' : 'Từ chối' ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <?= nl2br(htmlspecialchars($msg['body'], ENT_QUOTES)) ?>
                <?php endif; ?>
                <div class="text-[11px] opacity-60 mt-1"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- Input -->
        <div class="flex items-end gap-2.5 px-4 py-3.5 border-t border-gray-100 bg-white flex-shrink-0">
          <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
          <input type="hidden" id="convId"    value="<?= $activeConv['id'] ?>">
          <input type="hidden" id="lastMsgId" value="<?= !empty($messages) ? end($messages)['id'] : 0 ?>">
          <textarea id="msgInput" placeholder="Nhập tin nhắn..." rows="1"
                    style="flex:1;border:1.5px solid #e8ecf0;border-radius:12px;padding:10px 14px;font-size:.875rem;resize:none;outline:none;transition:.2s;max-height:100px;min-height:44px;font-family:inherit"
                    onfocus="this.style.borderColor='#4f46e5';this.style.boxShadow='0 0 0 3px rgba(79,70,229,.12)'"
                    onblur="this.style.borderColor='#e8ecf0';this.style.boxShadow='none'"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"></textarea>
          <button onclick="sendMsg()" title="Gửi"
                  class="flex items-center justify-center text-white border-0 cursor-pointer transition-all flex-shrink-0"
                  style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#4f46e5,#8b5cf6)"
                  onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>

      <?php else: ?>
        <div class="flex flex-col items-center justify-center h-full gap-3 text-gray-300">
          <i class="bi bi-chat-left-dots text-5xl opacity-25"></i>
          <div class="text-sm">Chọn một cuộc trò chuyện để bắt đầu</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($activeConv): ?>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const BASE=<?= json_encode($appUrl) ?>, convId=<?= $activeConv['id'] ?>;
const sellerId=<?= (int)$activeConv['seller_id'] ?>, meId=<?= (int)$me['id'] ?>;
function scrollBottom(){const e=document.getElementById('chatMessages');if(e)e.scrollTop=e.scrollHeight;}
scrollBottom();
function escHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
let isSending=false;
async function sendMsg(){
  if(isSending)return;
  const input=document.getElementById('msgInput'),body=input.value.trim();
  if(!body)return;
  isSending=true;input.value='';input.style.height='auto';
  try{
    const r=await fetch(BASE+'/chat/send',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`conversation_id=${convId}&body=${encodeURIComponent(body)}&_csrf=${document.getElementById('csrfToken').value}`});
    const d=await r.json();
    if(d.success){
      appendMsg({id:d.data.message_id,body:escHtml(d.data.body),is_me:true,sender_name:'',time:d.data.time});
      const cur=parseInt(document.getElementById('lastMsgId').value)||0;
      if(d.data.message_id>cur)document.getElementById('lastMsgId').value=d.data.message_id;
      scrollBottom();
    }
  }finally{isSending=false;}
}
function offerActHtml(id,status){
  if(status==='pending'){
    return sellerId===meId
      ?`<div class="flex gap-2" id="offer-act-${id}"><button onclick="respondOffer(${id},'accepted')" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-white bg-green-500 border-0 cursor-pointer">Đồng ý</button><button onclick="respondOffer(${id},'rejected')" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-white bg-red-500 border-0 cursor-pointer">Từ chối</button></div>`
      :`<div class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-yellow-100 text-yellow-700"><i class="bi bi-hourglass-split"></i>Chờ duyệt</div>`;
  }
  const cls=status==='accepted'?'bg-green-500':'bg-gray-400';
  const txt=status==='accepted'?'<i class="bi bi-check-circle-fill"></i>Đã chốt':'<i class="bi bi-x-circle-fill"></i>Từ chối';
  return `<div class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold text-white ${cls}">${txt}</div>`;
}
function appendMsg(m){
  if(document.getElementById('msg-'+m.id)){
    if(m.msg_type==='offer'){
      const act=document.getElementById('offer-act-'+m.id);
      if(act&&m.offer_status!=='pending')act.outerHTML=offerActHtml(m.id,m.offer_status);
    }return;
  }
  const wrap=document.getElementById('chatMessages'),el=document.createElement('div');
  el.id='msg-'+m.id;el.className='flex flex-col '+(m.is_me?'items-end':'items-start');
  let content=m.msg_type==='offer'
    ?`<div class="rounded-xl p-3 text-center text-gray-800" style="background:#fffcf2;border:1px solid #ffd43b;min-width:180px"><div class="font-bold text-sm text-yellow-600 mb-1"><i class="bi bi-tag-fill mr-1"></i>Đề nghị trả giá</div><div class="text-2xl font-black text-red-600 font-mono mb-2">${new Intl.NumberFormat('vi-VN').format(m.offer_price)} đ</div>${offerActHtml(m.id,m.offer_status)}</div>`
    :m.body.replace(/\n/g,'<br>');
  const bStyle=m.is_me?'style="background:linear-gradient(135deg,#4f46e5,#8b5cf6)"':'';
  const bCls=m.is_me?'text-white rounded-br-sm':'bg-white text-gray-800 border border-gray-100 rounded-bl-sm';
  el.innerHTML=`${!m.is_me?`<div class="text-[11px] text-gray-400 mb-1">${escHtml(m.sender_name)}</div>`:''}<div class="max-w-[68%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed break-words ${bCls}" ${bStyle}>${content}<div class="text-[11px] opacity-60 mt-1">${m.time}</div></div>`;
  wrap.appendChild(el);
}
async function respondOffer(msgId,status){
  if(!confirm('Bạn chắc chắn muốn '+(status==='accepted'?'ĐỒNG Ý':'TỪ CHỐI')+' mức giá này?'))return;
  try{
    const r=await fetch(BASE+'/api/chat/offer/respond',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`message_id=${msgId}&status=${status}&conversation_id=${convId}&_csrf=${document.getElementById('csrfToken').value}`});
    const d=await r.json();
    if(d.success){const act=document.getElementById('offer-act-'+msgId);if(act)act.outerHTML=offerActHtml(msgId,status);}
    else alert(d.message||'Lỗi');
  }catch(e){console.error(e);}
}
// === PUSHER WEB SOCKET REAL-TIME ===
const pusher = new Pusher('<?= $_ENV['PUSHER_APP_KEY'] ?? 'f50f24f5a8a8c171e1b1' ?>', {
  cluster: '<?= $_ENV['PUSHER_APP_CLUSTER'] ?? 'ap1' ?>'
});
const channel = pusher.subscribe('chat.' + meId);
channel.bind('new_message', function(data) {
  // Chỉ append nếu tin nhắn thuộc về cuộc hội thoại hiện tại đang mở
  if (data.conv_id == convId) {
    appendMsg(data);
    const cur = parseInt(document.getElementById('lastMsgId').value) || 0;
    if(data.id > cur) document.getElementById('lastMsgId').value = data.id;
    scrollBottom();
  } else {
    // Nếu tin nhắn của hội thoại khác, có thể update UI ở danh sách bên trái hoặc hiển thị toast
    // TODO: update unread counter or show toast (optional)
  }
});

document.getElementById('msgInput').addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px';});
</script>
<?php endif; ?>

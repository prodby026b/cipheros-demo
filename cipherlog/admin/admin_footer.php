<?php // admin/admin_footer.php ?>
</div><!-- end admin-content -->
</div><!-- end main -->
</div><!-- end admin-wrap -->

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.getElementById('main').classList.toggle('expanded');
}
function showToast(msg,type){
  type=type||'info';
  var icons={success:'ti-check',error:'ti-x',info:'ti-info-circle'};
  var colors={success:'var(--green)',error:'var(--red)',info:'var(--blue)'};
  var t=document.createElement('div');
  t.className='toast';
  t.style.borderColor=type==='success'?'rgba(0,255,157,.3)':type==='error'?'rgba(255,62,62,.3)':'rgba(0,170,255,.3)';
  t.innerHTML='<i class="ti '+icons[type]+'" style="color:'+colors[type]+';font-size:16px;flex-shrink:0"></i>'+msg;
  document.getElementById('toasts').appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},2800);
}
// Delete confirmation
function confirmDelete(url, name){
  if(confirm('Delete "'+name+'"? This cannot be undone.')) window.location = url;
}
// Search filter
function filterTable(inputId, tbodyId){
  var q=document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#'+tbodyId+' tr').forEach(function(row){
    row.style.display=row.textContent.toLowerCase().includes(q)?'':'none';
  });
}
// Toggle
document.querySelectorAll('.toggle').forEach(t=>{
  t.addEventListener('click',function(){this.classList.toggle('on');});
});
</script>
</body>
</html>

// *** Set your app base explicitly. ***
const __APP_BASE__ = '/online-game-center';       
const __API_BASE__ = __APP_BASE__ + '/api/';

async function api(path, method='GET', data=null, form=false){
  const url = __API_BASE__ + path;
  const opts = { method, headers:{} };
  if (data){
    if (form){
      const fd = new FormData();
      for (const k in data) fd.append(k, data[k]);
      opts.body = fd;
    } else {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(data);
    }
  }
  const res = await fetch(url, opts);
  const json = await res.json().catch(()=>({}));
  if (!res.ok) throw json;
  return json;
}
async function getMe(){ try{ const r=await api('auth.php?action=me'); return r.user; }catch(e){return null;} }
function requireLoginOrRedirect(){ getMe().then(u=>{ if(!u) location.href='./login.html'; }); }
window.__API_BASE__ = __API_BASE__;

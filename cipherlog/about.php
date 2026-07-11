<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
authStart();

$authorName = getSetting('author_name', 'prodby026b');
$authorBio  = getSetting('author_bio', 'Linux & Network educator. Documenting the deep stack — one command at a time.');
$postCount  = (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'", [])['c'];
$viewCount  = (int)queryOne("SELECT SUM(views) AS v FROM posts", [])['v'];
$subCount   = (int)queryOne("SELECT COUNT(*) AS c FROM subscribers WHERE status='active'", [])['c'];
$commCount  = (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='approved'", [])['c'];

$pageTitle = 'About — ' . getSetting('blog_name');
$pageDesc  = 'About ' . $authorName . ' — ' . $authorBio;
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div style="display:grid;grid-template-columns:1fr 260px;gap:48px;padding:56px 0 40px;align-items:start">
    <div>
      <div class="hero-tag" style="margin-bottom:16px"><i class="ti ti-user"></i>ABOUT</div>
      <h1 style="font-size:32px;font-weight:700;color:var(--text);margin-bottom:6px">
        Hey, I'm <span style="color:var(--green)"><?= e($authorName) ?></span>
      </h1>
      <div style="font-size:10px;color:var(--muted);letter-spacing:1.5px;margin-bottom:24px">LINUX & NETWORK EDUCATOR · CIPHER_LOG</div>

      <p style="font-size:13px;color:var(--muted);line-height:1.9;font-family:'Inter',sans-serif;margin-bottom:16px">
        <?= nl2br(e($authorBio)) ?>
      </p>
      <p style="font-size:13px;color:var(--muted);line-height:1.9;font-family:'Inter',sans-serif;margin-bottom:16px">
        I work daily with Linux servers, packet-level networking, and shell automation. Everything on this blog comes from real systems, real problems, and real fixes. No fluff, no copied man pages.
      </p>
      <p style="font-size:13px;color:var(--muted);line-height:1.9;font-family:'Inter',sans-serif;margin-bottom:28px">
        Topics I care about: <strong style="color:var(--text)">iptables</strong>, routing protocols, shell scripting, SSH hardening, <strong style="color:var(--text)">tcpdump</strong>, systemd, and the fundamental networking protocols that make the internet work.
      </p>

      <!-- Skills -->
      <div style="display:flex;flex-wrap:wrap;gap:7px;margin-bottom:28px">
        <?php
        $skills = [
            ['ti-brand-debian','Linux','green'],
            ['ti-network','TCP/IP','blue'],
            ['ti-shield','Security','red'],
            ['ti-terminal','Bash','purple'],
            ['ti-brand-python','Python','yellow'],
            ['ti-server','Servers','muted'],
            ['ti-database','MySQL','muted'],
            ['ti-brand-docker','Docker','blue'],
            ['ti-router','Routing','muted'],
            ['ti-vpn','VPN','green'],
        ];
        foreach ($skills as [$icon,$name,$color]):
            $colorVars = ['green'=>'var(--green)','blue'=>'var(--blue)','red'=>'var(--red)','purple'=>'var(--purple)','yellow'=>'var(--yellow)','muted'=>'var(--muted)'];
            $cv = $colorVars[$color];
        ?>
        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:10px;border:1px solid var(--border);color:var(--muted);transition:all .15s;cursor:default" onmouseover="this.style.borderColor='<?= $cv ?>';this.style.color='<?= $cv ?>'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
            <i class="ti <?= $icon ?>"></i><?= $name ?>
        </span>
        <?php endforeach; ?>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn-primary" href="<?= url('search.php') ?>"><i class="ti ti-arrow-right"></i>BROWSE POSTS</a>
        <button class="btn-outline" onclick="showToast('Opening GitHub...','info')"><i class="ti ti-brand-github"></i>GITHUB</button>
        <button class="btn-outline" onclick="showToast('Opening Telegram...','info')"><i class="ti ti-brand-telegram"></i>TELEGRAM</button>
      </div>
    </div>

    <!-- Profile card -->
    <div style="background:var(--card);border:1px solid var(--border);border-radius:10px;padding:24px;text-align:center">
      <div style="width:70px;height:70px;border-radius:50%;background:conic-gradient(var(--green),var(--blue),var(--green));display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#000;margin:0 auto 14px">
        <?= strtoupper(substr($authorName,0,2)) ?>
      </div>
      <div style="font-size:15px;font-weight:700;color:var(--green);letter-spacing:1px;margin-bottom:4px"><?= e($authorName) ?></div>
      <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:18px">root@cipherlog:~#</div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px">
        <?php foreach ([['Posts',$postCount],['Views',$viewCount>999?round($viewCount/1000,1).'K':$viewCount],['Subs',$subCount],['Comments',$commCount]] as [$lbl,$val]): ?>
        <div style="background:var(--bg3);border-radius:6px;padding:10px">
          <div style="font-size:16px;font-weight:700;color:var(--text)"><?= $val ?></div>
          <div style="font-size:8px;color:var(--muted);letter-spacing:1px"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="display:flex;flex-direction:column;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:5px;border:1px solid var(--border);font-size:10px;color:var(--muted);cursor:pointer;transition:all .15s" onmouseover="this.style.borderColor='var(--green)';this.style.color='var(--green)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'" onclick="showToast('Opening GitHub...','info')">
          <i class="ti ti-brand-github"></i>github.com/<?= e($authorName) ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:5px;border:1px solid var(--border);font-size:10px;color:var(--muted);cursor:pointer;transition:all .15s" onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'" onclick="showToast('Opening Telegram...','info')">
          <i class="ti ti-brand-telegram"></i>t.me/cipherlog
        </div>
        <a href="mailto:<?= e(getSetting('admin_email')) ?>" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:5px;border:1px solid var(--border);font-size:10px;color:var(--muted);text-decoration:none;transition:all .15s" onmouseover="this.style.borderColor='var(--green)';this.style.color='var(--green)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
          <i class="ti ti-mail"></i><?= e(getSetting('admin_email')) ?>
        </a>
        <a href="<?= url('api/rss.php') ?>" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:5px;border:1px solid var(--border);font-size:10px;color:var(--muted);text-decoration:none;transition:all .15s" onmouseover="this.style.borderColor='var(--yellow)';this.style.color='var(--yellow)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
          <i class="ti ti-rss"></i>RSS Feed
        </a>
      </div>
    </div>
  </div>

  <!-- Timeline -->
  <div style="max-width:720px;margin:0 auto;padding-bottom:64px">
    <div class="section-head"><div class="section-title">Blog Timeline</div></div>
    <?php
    $timeline = [
        ['June 2026',    'ti-flag',       'iptables Deep Dive Series', 'Launched a 3-part series on iptables, nftables migration, and advanced conntrack. Reached 2,300+ views on the first post in 48 hours.'],
        ['May 2026',     'ti-users',      'Hit 600 Subscribers',       'The newsletter crossed 600 active subscribers. Started sending weekly digests with new posts and CLI tips.'],
        ['March 2026',   'ti-chart-line', 'First 10K Monthly Views',   'The Bash Scripting post went viral on Reddit r/linux and pushed monthly traffic past 10,000 for the first time.'],
        ['January 2024', 'ti-rocket',     'CipherLog Launched',        'Published the first post: "Setting up a minimal Debian server from scratch." The blog has grown every month since.'],
    ];
    foreach ($timeline as [$date,$icon,$title,$desc]):
    ?>
    <div style="display:flex;gap:20px;padding-bottom:28px;position:relative">
      <div style="position:absolute;left:13px;top:28px;bottom:0;width:1px;background:var(--border)"></div>
      <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;color:var(--green);z-index:1"><i class="ti <?= $icon ?>"></i></div>
      <div style="flex:1;padding-bottom:4px">
        <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:4px"><?= $date ?></div>
        <div style="font-size:12px;font-weight:600;color:var(--text);margin-bottom:4px"><?= $title ?></div>
        <div style="font-size:11px;color:var(--muted);line-height:1.7;font-family:'Inter',sans-serif"><?= $desc ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

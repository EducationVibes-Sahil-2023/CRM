<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <h4>WhatsApp</h4>
    <iframe src="<?= $bridge ?>connect.html?user=<?= urlencode($user) ?>&token=<?= $token ?>"
            style="width:400px;height:660px;border:0"></iframe>
  </div>
  
  <div class="content">
    <h4>WhatsApp</h4>
    <?= $bridge ?>/messages?user=<?= urlencode($user) ?>&token=<?= $token ?>&number=919871159668&limit=20"?>
    <iframe src="<?= $bridge ?>/messages?user=<?= urlencode($user) ?>&token=<?= $token ?>&number=919871159668&limit=20"
            style="width:400px;height:660px;border:0"></iframe>
  </div>
</div>
<?php init_tail(); ?>
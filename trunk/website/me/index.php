<?php
require_once '../inc/header.php';

$q = $__database->query("
SELECT 
	chr.id, 
	chr.name, 
	w.world_name 
FROM 
	characters chr 
LEFT JOIN 
	users usr 
	ON 
		usr.ID = chr.userid 
LEFT JOIN 
	accounts acc 
	ON 
		acc.id = usr.account_id 
LEFT JOIN 
	world_data w 
	ON 
		w.world_id = chr.world_id 

WHERE 
	acc.username = '".$__database->real_escape_string($__url_useraccount->GetUsername())."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC
");

// printing table rows

$cache = array();

while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}
$q->free();

$has_characters = count($cache) != 0;
$main_character_info = $has_characters ? $cache[0] : null;
$main_character_name = $has_characters ? $main_character_info['name'] : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';
?>

	<div id="profile" class="row">
		<div id="header" class="span12" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center">
        	<div id="meta-nav">
            	<div class="row">
                	<div class="span12">
                    	<ul id="nav-left">
                        	<li><a id="posts" href="#"><span class="sprite icon post"></span><span class="count"><?php echo count($cache); ?></span> <span class="item">Characters</span></a></li>
                            <li><a id="likes" href="#"><span class="sprite icon badgestar"></span><span class="count">#</span> <span class="item">Achievements</span></a></li>
                            </ul>
                            
                        <ul id="nav-right">
                        </ul>
                   </div>
              </div>
          </div>
          
          <div id="profile-user-details">
          	 <div class="row">
            	<div class="span6 offset3" style="margin-bottom:70px;">
                	<div id="user-about" class="center">
                    	<h2><?php echo $__url_useraccount->GetNickname(); ?></h2>
<?php if ($__url_useraccount->GetBio() != null): ?>
                        <ul id="user-external">
                        	<li><span style="color: rgb(255, 255, 255); text-shadow: rgb(102, 102, 102) 1px 0px 3px;">
                        	<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png" style="position: relative; top: 4px;"/> <?php echo $__url_useraccount->GetBio(); ?></span></li>
                            
                        </ul>
<?php endif; ?>
                   </div>
               </div>
           </div>
           
           <div class="row">
           		<div class="span2 offset5 center" style="margin-bottom: -70px;">
                	<a href="//<?php echo $domain; ?>/stats/<?php echo $row['name']; ?>"><img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>" style="display:inline-block;background: rgb(255, 255, 255);
border-radius: 150px;
margin-bottom: -30px;
box-sizing: border-box;
-webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
border: 8px solid rgb(255, 255, 255);
border-image: initial;
text-align: center;" /> </a>
				</div>
			</div>
		</div>
	</div>
</div>
	
	<!-- Character Display -->
	<div id="character-wall">

<?php
if (count($cache) == 0) {
?>
		<p class="lead alert-error alert"><?php echo $__url_useraccount->GetUsername(); ?> hasn't added any characters yet!</p>
<?php
}

// printing table rows
foreach ($cache as $row) {
?>
		<div class="span2 character-brick" style="min-width:174px;margin-left: 20px;">
		<div class="caption"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" />&nbsp;<?php echo $row['name']; ?></div>
			<center>
				<br />
				<a href="//<?php echo $domain; ?>/stats/<?php echo $row['name']; ?>" style="text-decoration: none !important; font-weight: 300; color: inherit;">
					<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>"/>
				</a>
				<br />
			</center>
        </div>
        
<?php       
}
?>

	</div>

<?php require_once '../inc/footer.php'; ?>
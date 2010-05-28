<?php
//error_reporting(E_ALL);

 header("Content-Type: text/html; charset=ISO-8859-1");
 include_once("../util/dbconnection.php");
 include_once("../util/symbini.php");

 $proj = array_key_exists("proj",$_REQUEST)?$_REQUEST["proj"]:""; 
 $editMode = array_key_exists("emode",$_REQUEST)?$_REQUEST["emode"]:""; 
 $projManager = new ProjectManager($proj);
 
 $isEditable = 0;
 if($isAdmin || in_array("proj-".$projManager->getProjectId(),$userRights)){
	$isEditable = 1;
 }
 
 if($isEditable){
 	if(array_key_exists("projsubmit",$_REQUEST)){
 		$projEditArr = Array();
 		$projEditArr["projname"] = $_REQUEST["projname"];
 		$projEditArr["managers"] = $_REQUEST["managers"];
 		$projEditArr["briefdescription"] = $_REQUEST["briefdescription"];
 		$projEditArr["fulldescription"] = $_REQUEST["fulldescription"];
 		$projEditArr["notes"] = $_REQUEST["notes"];
 		$projEditArr["sortsequence"] = $_REQUEST["sortsequence"];
 		$projManager->submitProjEdits($projEditArr);
 	}
 }
 
 ?>
<html>
<head>
	<title><?php echo $defaultTitle; ?> Species Lists</title>
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<script type="text/javascript">
	
		function toggleById(target){
		  	var obj = document.getElementById(target);
			if(obj.style.display=="none"){
				obj.style.display="block";
			}
		 	else {
		 		obj.style.display="none";
		 	}
		}

		function toggleStaticInfoBox(anchorObj){
			var obj = document.getElementById("staticlistpopup");
			var pos = findPos(anchorObj);
			var posLeft = pos[0];
			if(posLeft > 550){
				posLeft = 550;
			}
			obj.style.left = posLeft - 40;
			obj.style.top = pos[1] + 25;
			if(obj.style.display=="block"){
				obj.style.display="none";
			}
			else {
				obj.style.display="block";
			}
			var targetStr = "document.getElementById('staticlistpopup').style.display='none'";
			var t=setTimeout(targetStr,25000);
		}

		function toggleDynamicInfoBox(anchorObj){
			var obj = document.getElementById("dynamiclistpopup");
			var pos = findPos(anchorObj);
			var posLeft = pos[0];
			if(posLeft > 550){
				posLeft = 550;
			}
			obj.style.left = posLeft - 40;
			obj.style.top = pos[1] + 25;
			if(obj.style.display=="block"){
				obj.style.display="none";
			}
			else {
				obj.style.display="block";
			}
			var targetStr = "document.getElementById('dynamiclistpopup').style.display='none'";
			var t=setTimeout(targetStr,20000);
		}

		function findPos(obj){
			var curleft = 0; 
			var curtop = 0;
			if(obj.offsetParent) {
				do{
					curleft += obj.offsetLeft;
					curtop += obj.offsetTop;
				}while(obj = obj.offsetParent);
			}
			return [curleft,curtop];
		}	
	</script>
</head>

<body <?php if($editMode) echo "onload=\"toggleById('projeditor');\"";?>>

	<?php
	$displayLeftMenu = (isset($projects_indexMenu)?$projects_indexMenu:"true");
	include($serverRoot."/util/header.php");
	if(isset($projects_indexCrumbs)){
		echo "<div class='navpath'>";
		echo "<a href='../index.php'>Home</a> &gt; ";
		echo $projects_indexCrumbs;
		echo " <b>$defaultTitle Project</b>"; 
		echo "</div>";
	}
	?>
	
	<!-- This is inner text! -->
	<div id="innertext">

	<?php
	
	if(!$proj){
		echo "<h1>".$defaultTitle." Projects</h1>"; 
		$projectArr = $projManager->getProjectList();
		foreach($projectArr as $pid => $projList){
			echo "<h2><a href='index.php?proj=".$pid."'>".$projList["projname"]."</a></h2>\n";
			if($projList["managers"]) echo "<div><b>Managers:</b> ".$projList["managers"]."</div>\n";
			echo "<div style='margin:10px;'>".$projList["descr"]."</div>\n";
		}
	}
	else{
		if($isEditable){
			echo "<div style=\"float:right;cursor:pointer;\" onclick=\"toggleById('projeditor');\" title=\"Toggle Editing Functions\">";
			echo "<img style='border:0px;' src='../images/edit.png'/>";
			echo "</div>";
		}
		$projectArr = $projManager->getProjectData();
		foreach($projectArr as $pid => $projArr){
			echo "<h1>".$projArr["projname"]."</h1>\n";
			echo "<div style='margin:10px;'><b>Project Managers:</b> ".$projArr["managers"]."</div>";
			echo "<div style='margin:10px;'>".$projArr["fulldescription"]."</div>";
			echo "<div style='margin:10px;'>".$projArr["notes"]."</div>";
		}

		if($isEditable){ ?>
			<form action='index.php' method='get' name='projeditorform'>
				<fieldset id="projeditor" style="display:none;background-color:#FFF380;">
					<legend><b>Project Editor</b></legend>
					<div>
						Project Name:
						<input type="text" name="projname" value="<?php echo $projArr["projname"];?>" style="width:75px;"/>
					</div>	
					<div>
						Managers: 
						<input type="text" name="managers" value="<?php echo $projArr["managers"];?>" style="width:300px;"/>
					</div>
					<div>
						Brief Description: 
						<textarea rows="2" cols="45" name="briefdescription" maxsize="300"><?php echo $projArr["briefdescription"];?></textarea>
					</div>
					<div>
						Full Description: 
						<textarea rows="3" cols="45" name="fulldescription" maxsize="1000"><?php echo $projArr["fulldescription"];?></textarea>
					</div>
					<div>
						Notes:
						<input type="text" name="notes" value="<?php echo $projArr["notes"];?>" style="width:300;"/>
					</div>
					<div>
						Sort Sequence: 
						<input type="text" name="sortsequence" value="<?php echo $projArr["sortsequence"];?>" style="width:30;"/>
					</div>
					<div>
						<input type="hidden" name="proj" value="<?php echo $projManager->getProjectId();?>">
						<input type="submit" name="projsubmit" value="Submit Edits" />
					</div>
				</fieldset>
			</form>
		<?php }?>

        <div style="margin:20px;">
            <?php
            $staticList = $projManager->getStaticChecklists();
			if($staticList){
			?>
				<h3>Static Species Lists
					<span onclick="toggleStaticInfoBox(this);" title="What is a Static Species List?" style="cursor:pointer;">
						<img src="../images/qmark.jpg" style="height:15px;"/>
					</span> 
					<a href="../checklists/clgmap.php?proj=<?php echo $projManager->getProjectId();?>" title="Map Checklists">
						<img src='../images/world40.gif' style='width:14px;border:0' />
					</a>
				</h3>
				<div id="staticlistpopup" class="genericpopup">
					<img src="../images/uptriangle.png" style="position: relative; top: -22px; left: 30px;" />
		            Static Species Lists are pre-compiled by floristic researchers.
		            This is a very controlled method of building a species list, which allows for  
		            specific specimens to be linked to species name and serve as vouchers. 
		            Vouchers are proof that the species actually occurs in the given area. If there is any doubt, one
		            can inspect these specimens for verification or make identification annotations when necessary.
				</div>
				<ul>
				<?php 	
					foreach($staticList as $key=>$value){
	            ?>
					<li>
						<a href='../checklists/checklist.php?cl=<?php echo $key."&proj=".$projManager->getProjectId(); ?>'>
							<?php echo $value; ?>
						</a> 
						<a href='../ident/key.php?cl=<?php echo $key; ?>&proj=<?php echo $projManager->getProjectId(); ?>&taxon=All+Species'>
							<img style='width:12px;border:0px;' src='../images/key.jpg'/>
						</a>
					</li>
					<?php } ?>
				</ul>
			<?php }
                $dynamicList = $projManager->getDynamicChecklists();
			if($dynamicList){
			?>
				<h3>Dynamic Species Lists 
					<span onclick="toggleDynamicInfoBox(this);" title="What is a Dynamic Species List?" style="cursor:pointer;">
						<img src="../images/qmark.jpg" style="height:15px;"/>
					</span> 
					<a href="../checklists/clgmap.php?proj=" title="Map checklists">
						<img src="../images/world40.gif" style="width:14px;border:0" />
					</a>
				</h3>
				<div id="dynamiclistpopup" class="genericpopup">
					<img src="../images/uptriangle.png" style="position: relative; top: -22px; left: 30px;" />
		            Dynamic checklists are generated directly from specimen data each time the checklist is accessed.
		            Since these lists are built on-the-fly, they take a bit longer to display. 
		            The addition or annotation of a specimen at any of the participating research institutions 
		            will automatically adjust the dynamic species list without the name having to be explicitly added.
				</div>
				<ul>
				<?php 	
				foreach($dynamicList as $key=>$value){
	            ?>
            
					<li>
						<a href='../checklists/checklist.php?cl=<?php echo $key;?>'><?php echo $value;?></a> 
					</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	<?php
	}
	?>
	
	</div>
	<?php
	include($serverRoot."/util/footer.php");
	?>

	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
		try {
			var pageTracker = _gat._getTracker("<?php echo $googleAnalyticsKey; ?>");
			pageTracker._trackPageview();
		} catch(err) {}
	</script>
</body>
</html>
<?php
 
 class ProjectManager {

	private $con;
	private $projId;

 	public function __construct($proj){
 		$this->con = MySQLiConnectionFactory::getCon("readonly");
		if(is_numeric($proj)){
			$this->projId = $proj;
		}
		else{
			$sql = "SELECT p.pid FROM fmprojects p WHERE (p.projname = '".$proj."')";
			$rs = $this->con->query($sql);
			if($row = $rs->fetch_object()){
				$this->projId = $row->pid;
			}
			$rs->close();
		}
 	}
 	
 	public function __destruct(){
		if(!($this->con === null)) $this->con->close();
	}
	
	public function getProjectId(){
		return $this->projId;
	}
	
	public function getProjectList(){
		$returnArr = Array();
		$sql = "SELECT p.pid, p.projname, p.managers, p.briefdescription ".
			"FROM fmprojects p ".
			"ORDER BY p.SortSequence, p.projname";
		$rs = $this->con->query($sql);
		while($row = $rs->fetch_object()){
			$projId = $row->pid;
			$returnArr[$projId]["projname"] = $row->projname;
			$returnArr[$projId]["managers"] = $row->managers;
			$returnArr[$projId]["descr"] = $row->briefdescription;
		}
		$rs->close();
		return $returnArr;
	}
	
	public function getProjectData(){
		$returnArr = Array();
		$sql = "SELECT p.pid, p.projname, p.managers, p.briefdescription, p.fulldescription, p.notes, p.sortsequence ".
			"FROM fmprojects p INNER JOIN fmchklstprojlink cpl ON p.pid = cpl.pid ".
			"WHERE (p.pid = ".$this->projId.") ".
			"ORDER BY p.SortSequence, p.projname";
		//echo $sql;
		$rs = $this->con->query($sql);
		if($row = $rs->fetch_object()){
			$this->projId = $row->pid;
			$returnArr[$this->projId]["projname"] = $row->projname;
			$returnArr[$this->projId]["managers"] = $row->managers;
			$returnArr[$this->projId]["briefdescription"] = $row->briefdescription;
			$returnArr[$this->projId]["fulldescription"] = $row->fulldescription;
			$returnArr[$this->projId]["notes"] = $row->notes;
			$returnArr[$this->projId]["sortsequence"] = $row->sortsequence;
		}
		$rs->close();
		return $returnArr;
	}

	public function submitProjEdits($projArr){
		$conn = MySQLiConnectionFactory::getCon("write");
		$sql = "";
		foreach($projArr as $field=>$value){
			$sql .= ",$field = \"".$value."\"";
		}
		$sql = "UPDATE fmprojects SET ".substr($sql,1)." WHERE pid = ".$this->projId;
		//echo $sql;
		$conn->query($sql);
		$conn->close();
	}
	
	public function getStaticChecklists(){
		$returnArr = Array();
		$sql = "SELECT c.clid, c.name ".
			"FROM fmchklstprojlink cpl INNER JOIN fmchecklists c ON cpl.clid = c.clid ".
			"WHERE (c.Type = 'static') AND (cpl.pid = ".$this->projId.") ".
			"ORDER BY c.SortSequence, c.name";
		$rs = $this->con->query($sql);
		echo "<ul>";
		while($row = $rs->fetch_object()){
			$returnArr[$row->clid] = $row->name;
		}
		echo "</ul>";
		$rs->close();
		return $returnArr;
	}
	
	public function getDynamicChecklists(){
		$returnArr = Array();
		$sql = "SELECT c.clid, c.name ".
			"FROM fmchklstprojlink cpl INNER JOIN fmchecklists c ON cpl.clid = c.clid ".
			"WHERE (c.Type = 'dynamic') AND (cpl.pid = ".$this->projId.") ".
			"ORDER BY c.SortSequence, c.name";
		$rs = $this->con->query($sql);
		echo "<ul>";
		while($row = $rs->fetch_object()){
			$returnArr[$row->clid] = $row->name;
		}
		echo "</ul>";
		$rs->close();
		return $returnArr;
	}
 }

 ?>
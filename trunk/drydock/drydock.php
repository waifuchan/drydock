<?php

				$sm->display($threadtpl,$cid);
				if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $sm->display("modscript.tpl",$cid); }
				$sm->display("bottombar.tpl",$cid);

			$sm->display($tpl,$cid);
			if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $sm->display("modscript.tpl",$cid); }
			echo $sm->display("bottombar.tpl",$cid);

			//$sm->display($tpl,$cid);
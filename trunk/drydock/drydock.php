<?php

				$sm->display($threadtpl,$cid);
				if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $sm->display("modscript.tpl", null); }
				$sm->display("bottombar.tpl",null);

			$sm->display($tpl,$cid);
			if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $sm->display("modscript.tpl",null); }
			echo $sm->display("bottombar.tpl",null);

			//$sm->display($tpl,$cid);
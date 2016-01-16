<?php
namespace content_cp\permissions;

class controller extends \content_cp\home\controller
{
	function config()
	{
		$this->route_check_true = true;
		$myChild = $this->child();
		if($myChild)
		{
			$this->display_name	= 'content_cp/permissions/display_child.html';
			switch ($myChild)
			{
				case 'add':
					$this->post($myChild)->ALL('/^[^\/]*\/[^\/]*$/');
					break;

				case 'edit':
					$this->put($myChild)->ALL('/^[^\/]*\/[^\/]*$/');
					break;

				case 'delete':
					$this->post($myChild)->ALL('/^[^\/]*\/[^\/]*$/');
					$this->get($myChild)->ALL('/^[^\/]*\/[^\/]*$/');
					break;

				default:
					return false;
					break;
			}
		}
		else
		{

		}
	}


	/**
	 * return the list of contents exist in current project and addons
	 * @return [type] [description]
	 */
	public function permContentsList()
	{
		// get all content exist in saloos and current project
		$addons   = glob(addons. "content_*", GLOB_ONLYDIR);
		$project  = glob(root. "content_*", GLOB_ONLYDIR);
		$contents = array_merge($addons, $project);
		$myList   = [];

		foreach ($contents as $myContent)
		{
			$myContent = preg_replace("[\\\\]", "/", $myContent);
			$myContent = substr( $myContent, ( strrpos( $myContent, "/" ) + 1) );
			$myContent = substr( $myContent, ( strrpos( $myContent, "_" ) + 1) );
			array_push($myList, $myContent);
		}
		$myList = array_flip($myList);
		unset($myList['account']);
		$myList = array_flip($myList);

		return $myList;
	}

	/**
	 * return the modules of each part of system
	 * first check if function declare then return the permissions module of this content
	 * @param  [string] $_content content name
	 * @return [array]  return the permission modules list
	 */
	public function permModulesList($_content)
	{
		$myList      = [];
		$contentName = '\content_'. $_content. '\home\controller';
		if(method_exists($contentName, 'permModules'))
		{
			// if module exist call it
			$contentInstance = new $contentName;
			$myList          = $contentInstance->permModules();
			if(!is_array($myList))
			{
				$myList = [];
			}

			// recheck return value from permission modules list func
			foreach ($myList as $permLoc => $permValue)
			{
				if(is_array($permValue))
				{
					$permCond = ['select', 'add', 'edit', 'delete'];
					foreach ($permValue as $key => $value)
					{
						// remove meta
						unset($myList[$permLoc][$key]);
						if(in_array($value, $permCond))
						{
							$myList[$permLoc][$value] = 'hide';
						}
					}

					// $myList[$permLoc] = array_flip($permValue);
				}
				else
				{
					$myList[$permLoc] = null;
				}
			}
		}
		var_dump($myList);
		// $myList = array_flip($myList);


		return $myList;
	}
}
?>
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
	public function permContents($_fill = false)
	{
		// get all content exist in saloos and current project
		$addons     = glob(addons."content_*", GLOB_ONLYDIR);
		$project    = glob(root. "content_*", GLOB_ONLYDIR);
		$contents   = array_merge($addons, $project);
		$myContents = ['cp' => null, 'account' => null];

		foreach ($contents as $key => $myContent)
		{
			$myContent = preg_replace("[\\\\]", "/", $myContent);
			$myContent = substr( $myContent, ( strrpos( $myContent, "/" ) +1 ) );
			$myContent = substr( $myContent, ( strrpos( $myContent, "_" ) +1 ) );
			$myContents[$myContent] = null;
			if($_fill)
			{
				$myContents[$myContent] = ['enable' => true, 'modules' => null, 'roles' => null];
			}
		}

		$myContents['SiteContent'] = null;
		if($_fill)
		{
			$myContents['SiteContent'] = ['enable' => true, 'modules' => null, 'roles' => null];
		}



		return $myContents;
	}

	/**
	 * return the modules of each part of system
	 * @param  [type] $_content content name
	 * @return [type]           [description]
	 */
	public function permModules($_content = null)
	{
		$mylist = null;
		switch ($_content)
		{
			case 'cp':
				$mylist	= [
							// 'home',
							'posts',
							'categories',
							'pages',
							'tags',
							'attachments',
							// 'filecategories',
							'users',
							'tools',
							'permissions',
							'options',
							// 'profile',
						];

				// get features value from view and fix it later
				$features = [];
				if(isset($this->data->feature) && is_array($this->data->feature))
					$features = $this->data->feature;

				foreach ($features as $feature => $enable)
				{
					// if option is not true continue to next
					if(!$enable)
						continue;

					// else switch on enabled feature
					switch ($feature)
					{
						case 'book':
							$mylist = array_push($mylist, $feature);
							$mylist = array_push($mylist, 'bookcategories');
							break;

						case 'socialnetworks':
						case 'visitors':
						default:
							$mylist = array_push($mylist, $feature);
							break;
					}
				}
				// var_dump($mylist);
				$mylist = $this->model()->permModulesFill($_content, $mylist);
				// var_dump($mylist);

				break;

			case 'account':
			case 'files':
				break;


			default:
				break;
		}

		// $mylist2 = $this->model()->permModulesFill($_content, $mylist);
		return $mylist;

		// $mylist = array_flip($mylist);
		$default_values = [
							'select' => true,
							'add'    => false,
							'edit'   => true,
							'delete' => true,
						];

		$mylist = array_fill_keys($mylist, $default_values);
		return $mylist;
	}
}
?>
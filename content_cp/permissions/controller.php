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
	 * return the modules of each part of system
	 * @param  [type] $_content content name
	 * @return [type]           [description]
	 */
	public function permModules($_content = null)
	{
		$mylist = false;
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

				break;

			case 'account':
			case 'files':
				break;


			default:
				break;
		}

		// $mylist = array_flip($mylist);
		$default_values = [
							'select' => true,
							'add'    => false,
							'edit'   => true,
							'delete' => true,
						];
		$mylist2 = $this->model()->permModulesCp($mylist);
		return $mylist2;

		$mylist = array_fill_keys($mylist, $default_values);
		return $mylist;
	}
}
?>
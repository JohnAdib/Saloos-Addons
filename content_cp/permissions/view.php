<?php
namespace content_cp\permissions;

class view extends \content_cp\home\view
{
	public function options()
	{
		$this->data->page['desc']     = T_('Define or edit user permissions to allow or block access to special pages');
		$this->data->page['haschild'] = false;
		$myChild                      = $this->child();
		if($myChild)
		{
			$this->data->page['title'] = T_('Edit'). ' '.T_('permission') .' '. T_($myChild);
			// $this->data->site['title'] = " -- " .$myChild;

			// get all content exist in saloos and current project
			$addons    = glob(addons."content_*", GLOB_ONLYDIR);
			$project  = glob(root. "content_*", GLOB_ONLYDIR);
			$contents = array_merge($addons, $project);
			$this->data->permissions = ['cp' => [], 'account' => []];
			// var_dump($contents);
			foreach ($contents as $key => $myContent)
			{
				$myContent = preg_replace("[\\\\]", "/", $myContent);
				$myContent = substr( $myContent, ( strrpos( $myContent, "/" ) +1 ) );
				$myContent = substr( $myContent, ( strrpos( $myContent, "_" ) +1 ) );
				$this->data->permissions[$myContent] = ['enable' => true, 'modules' => null, 'roles' => null];
			}
			$this->data->permissions['SiteContent']   = ['enable' => false, 'modules' => null, 'roles' => null];
			$this->data->permissions['cp']['modules'] = $this->permModules('cp');


			foreach ($contents as $key => $myContent)
			{
				// foreach ($myContent as $module => )

			}

			// var_dump($this->data->permissions);


			// $this->data->permissions = $contents;
		}
		else
		{
			// $this->data->type = \lib\utility::get('name');
			$this->data->datarow = $this->model()->draw_permissions();
			// var_dump($this->data->datarow);
		}
	}
}
?>
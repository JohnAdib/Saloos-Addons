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


			$this->data->permissions = $this->permContents(true);
			// $this->data->permissions['SiteContent']   = ['enable' => false, 'modules' => null, 'roles' => null];
			$this->data->permissions['cp']['modules'] = $this->permModules('cp');
			// var_dump($this->data->permissions);


			// foreach ($contents as $key => $myContent)
			// {
			// 	// foreach ($myContent as $module => )

			// }

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
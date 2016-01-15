<?php
namespace content_cp\permissions;

use \lib\utility;
use \lib\debug;

class model extends \content_cp\home\model
{
	function get_delete()
	{
		$this->qryPermission('delete');
		// var_dump(3);exit();
		// $this->delete( $this->sql()->table('posts')->where('id', $this->childparam('delete')));
	}

	function delete_delete()
	{
		$this->qryPermission('delete');
	}

	function post_delete()
	{
		$this->qryPermission('delete');
	}

	function post_add()
	{
		$this->qryPermission('add');
	}

	function put_edit()
	{
		$this->qryPermission('edit');
		// var_dump(4);exit();
	}


	protected function qryCreator($_type = null, $_value = null)
	{
		$qry = $this->sql()->table('options')
			->where('option_cat',  'permissions')
			// ->and('option_key',    'permissions')
			// ->and('option_status', 'enable')
			->and('post_id',       '#NULL')
			->and('user_id',       '#NULL');

		switch ($_type)
		{
			case 'add':
			case 'edit':
				$qry = $qry->and('option_value', $_value);
				break;

			case 'delete':
				$qry = $qry->and('option_value', $_value);
				$qry = $qry->and('option_status', 'enable');
				break;

			default:
				break;
		}
		return $qry;
	}


	/**
	 * create a related query and run it
	 * @param  [type] $_type [description]
	 * @return [type]        [description]
	 */
	protected function qryPermission($_type)
	{
		$newPerm = utility::post('pName');
		switch ($_type)
		{
			case 'add':
				if(!$newPerm)
				{
					debug::warn(T_("First you must enter name of permission"));
					return;
				}
				// check permission exist or not
				$qryExist = $this->qryCreator($_type);
				$qryExist = $qryExist->select()->num();
				// if exist show related message
				if($qryExist)
				{
					debug::warn(T_("This permission name exist!"). " "
						. T_("You can edit this permission"));
					return;
				}

				// get last id in permissions
				$qryMaxID = $this->qryCreator();
				$qryMaxID = $qryMaxID->field('#max(option_key) as id')
					->select()->assoc('id');

				$qryMaxID += 1;

				$qryAdd = $this->qryCreator();
				$qryAdd = $qryAdd
					->set('option_cat',    'permissions')
					->set('option_key',    $qryMaxID)
					->set('option_value',  $newPerm)
					->set('option_status', 'enable')
					->insert();

				$qryAdd = $qryAdd;
				break;


			case 'delete':
				$delParam = $this->childparam('delete');
				// if user pass child param, get this param and update status of permission
				if($delParam)
				{
					$qryDel = $this->qryCreator($_type, $delParam);
					$qryDel = $qryDel->set('option_status', 'disable')->update();
				}
				break;


			case 'edit':
				$editParam  = $this->childparam('edit');
				$permResult = [];
				$permCond   = ['select', 'add', 'edit', 'delete'];

				if($editParam)
				{
					foreach ($this->permContentsList() as $myContent)
					{
						// step1: get and fill content enable status
						$postValue = utility::post('content-'.$myContent);
						if($postValue === 'on')
							$permResult[$myContent]['enable'] = true;
						else
							$permResult[$myContent]['enable'] = false;

						// step2: fill content modules status
						foreach ($this->permModulesList($myContent) as $myLoc)
						{
							foreach ($permCond as $cond)
							{
								$locName = $myContent. '-'. $myLoc.'-'. $cond;
								$postValue = utility::post($locName);
								if($postValue === 'on')
									$permResult[$myContent]['modules'][$myLoc][$cond] = true;
								// else
									// $permResult[$myContent]['modules'][$myLoc][$cond] = null;
							}
						}
					}
					$permResult = json_encode($permResult, JSON_FORCE_OBJECT);

					$qryEdit = $this->qryCreator($_type, $editParam);
					$qryEdit = $qryEdit->set('option_meta', $permResult)->update();
				}
				break;


			default:
				break;
		}


		$this->commit(function($_type, $_permName)
		{
			switch ($_type)
			{
				case 'add':
					debug::true(T_("Insert Successfully"));
					$this->redirector()->set_url('permissions/' . $_permName);
					break;

				case 'delete':
					debug::true(T_("Delete Successfully"));
					break;

				case 'edit':
					debug::true(T_("Update Successfully"));
					break;

				default:
					break;
			}
		}, $_type, $newPerm);

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction error").': ');
		} );
	}


	/**
	 * draw list of permissions
	 * @return [type] return array contain list of permission and detail of it
	 */
	public function draw_permissions()
	{
		$pType = utility::get('name');

		$qry_result  = [];
		$qry = $this->sql()->table('options')
					->where('user_id', 'IS', 'NULL')
					->and('post_id', 'IS', "NULL")
					->and('option_cat', 'permissions')
					->and('option_status',"enable")
					// ->and('option_key', 'permissions')

					// ->groupOpen('g_status')
					// ->and('option_status', '=', "'enable'")
					// ->or('option_status', 'IS', "NULL")
					// ->or('option_status', "")
					// ->groupClose('g_status')
					;



		$datatable = $qry->select()->allassoc();

		foreach ($datatable as $key => $row)
		{
			$myMeta  = $row['option_meta'];
			if(substr($myMeta, 0,1) == '{')
			{
				$myMeta = json_decode($myMeta, true);
			}
			$qry_result[$row['option_value']] = $myMeta;
		}

		// on first level return result
		if(!$pType)
		{
			return $qry_result;
		}
		else
		{
			// var_dump($qry_result[$pType]);
			return $qry_result[$pType];
		}
	}

	/**
	 * read permission data and fill in array
	 * @param  [type] $_list [description]
	 * @return [type]        [description]
	 */
	public function permModuleFill()
	{
		$permResult       = [];
		$permContentsList = $this->permContentsList();

		// 1. get the name of permission
		$myChild    = $this->child();
		if($myChild === 'edit')
			$myChild = $this->childparam('edit');

		// 2. get perm data from table
		$qry = $this->sql()->table('options')
				->where('user_id',    'IS', 'NULL')
				->and('post_id',      'IS', "NULL")
				->and('option_cat',   'permissions')
				->and('option_value',  $myChild)
				->and('option_status', "enable");

		$datarow = $qry->select()->assoc('option_meta');
		if(substr($datarow, 0, 1) == '{')
		{
			$datarow = json_decode($datarow, true);
		}

		// 3. fill the result
		foreach ($permContentsList as $myContent)
		{
			// 3.1 fill null
			$permResult[$myContent] =
			[
				'enable'  => null,
				'modules' => null,
				'roles'   => null
			];

			// 3.2 set enable status
			if(isset($datarow[$myContent]['enable']) && $datarow[$myContent]['enable'])
			{
				$permResult[$myContent]['enable'] = true;
			}

			// 3.3 get modules list of specefic content and fill it with db values
			$permModulesList = $this->permModulesList($myContent);
			foreach ($permModulesList as $loc)
			{
				if(isset($datarow[$myContent]['modules'][$loc])
					&& is_array($datarow[$myContent]['modules'][$loc]))
					$permResult[$myContent]['modules'][$loc] = $datarow[$myContent]['modules'][$loc];
				else
					$permResult[$myContent]['modules'][$loc] = null;
			}
		}

		return $permResult;
	}
}
?>
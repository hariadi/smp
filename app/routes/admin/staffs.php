<?php

Route::collection(array('before' => 'auth,csrf', 'after' => 'log'), function() {

	/*
		List staffs
	*/

	Route::get(array('admin/staffs', 'admin/staffs/(:num)'), function($page = 1) {

		$input = Input::get(array(
			'term',
			'division',
			'status',
			'missing',
		));

		$filter = array();
		$search = false;
		$staffs = false;

		if (!$input['status']) {
			$input['status'] = 'all';
		}

		if (empty($input['term'])) {
			$input['term'] = null;
		}

		if ($input['term']) {
			$validator = new Validator($input);

			$validator->check('term')->is_max(3, __('site.search_missing', 3));

			if($errors = $validator->errors()) {
				Input::flash();
				Notify::warning($errors);
				return Response::redirect(Uri::current());
			}

			Registry::set('admin_search_term', $input['term']);
			$search = true;
		}

		//dd($input['term']);
		$cari = new Cari($input['term']);
		$input = array_merge($input, $cari->result());

		//dd($input);

		foreach (['division', 'branch', 'sector', 'unit'] as $org) {
			if (array_key_exists($org, $input)) {
				$vars[$org] = $filter[$org] = $input[$org];
				$search = true;
			}
		}

		//dd($input, $filter);

		if ($input['status']) {
			$filter['status'] = $input['status'];
			$search = true;
		}

		if ($search) {
			$staffs = Staff::search($input['term'], $page, Config::meta('staffs_per_page'), true, $filter);
		} else {
			$staffs = Staff::paginate($page, Config::meta('staffs_per_page'));
		}

		$vars['messages'] = Notify::read();
		$vars['token'] = Csrf::token();
		$vars['staffs'] = $staffs;
		$vars['divisions'] = Division::listing();
		$vars['status'] = 'active';
		$vars['roles'] = array(
			'administrator' => __('global.administrator'),
			'editor' => __('global.editor'),
			'staff' => __('global.staff')
		);

		return View::create('staffs/index', $vars)
			->partial('header', 'partials/header')
			->partial('search', 'partials/search', $vars)
			->partial('footer', 'partials/footer');
	});

	/*
		Edit staff
	*/
	Route::get('admin/staffs/edit/(:num)', function($id) {
		$vars['messages'] = Notify::read();
		$vars['token'] = Csrf::token();
		$vars['admin'] = Auth::user();

		if (!$vars['staff'] = Staff::find($id)) {
			Notify::warning(__('staffs.not_found'));

			return Response::redirect('admin/staffs');
		}

		if ($vars['admin']->role == 'staff' && $vars['admin']->id != $id) {

			Notify::warning(__('staffs.noroleedit'));

			return Response::redirect('admin/staffs/');
		}


		if ($vars['admin']->role == 'editor' && !in_array($vars['staff']->division, $vars['admin']->roles)) {

			Notify::warning(__('staffs.noroleedit'));

			return Response::redirect('admin/staffs/');
		}


		$vars['tags'] = [];

		// get current staff division role
		if ($stafftags = StaffTag::where('staff', '=', $id)->get()) {
			foreach ($stafftags as $tag) {

				$vars['tags'][] = Tag::find($tag->tag)->title;

			}
		}

		$vars['revisions'] = Revision::staffid($id);
		$vars['fields'] = Extend::fields('staff', $id);

		$division_roles = array();
		$vars['tab'] = 'profile'; //default tab to open

		$input = array_filter(
			filter_var_array(
				Input::get(array('edit')),
				array(
					'edit' => FILTER_SANITIZE_SPECIAL_CHARS
					)
			)
		);

		if ($input) {
			$vars['tab'] = $input['edit'];
		}

		if ($report = Staff::find($vars['staff']->report_to)) {
			$vars['staff']->report_to = $report->display_name;
		}

		if ($pa = Staff::find($vars['staff']->personal_assistant)) {
			$vars['staff']->personal_assistant = $pa->display_name;
		}

		if ($branch = Branch::find($vars['staff']->branch)) {
			$vars['staff']->branch = $branch->title;
		}

		if ($sector = Sector::find($vars['staff']->sector)) {
			$vars['staff']->sector = $sector->title;
		}

		if ($unit = Unit::find($vars['staff']->unit)) {
			$vars['staff']->unit = $unit->title;
		}

		// get current staff division role
		if ($staff_roles = Role::where('staff', '=', $id)->get(array('division'))) {
			foreach ($staff_roles as $div) {
			$division_roles[] = $div->division;

			}
		}

		$vars['division_roles'] = $division_roles;

		/*
		$hierarchies = Hierarchy::where('staff', '=', $id)->fetch();

		$vars['staff']->division = $hierarchies->division;
		$vars['staff']->branch = $hierarchies->branch;
		$vars['staff']->sector = $hierarchies->sector;
		$vars['staff']->unit = $hierarchies->unit;
		*/
		foreach (array('Scheme', 'Division', 'Branch', 'Sector', 'Unit') as $hierarchy) {
			$vars[strtolower($hierarchy) . 's'] = $hierarchy::dropdown();
			$vars[strtolower($hierarchy) . 's'] = array_unshift_assoc($vars[strtolower($hierarchy) . 's'], '0', __('staffs.please_select'));
		}

		$vars['genders'] = array(
			'M' => __('staffs.male'),
			'F' => __('staffs.female')
		);

		$vars['statuses'] = array(
			'inactive' => __('global.inactive'),
			'active' => __('global.active')
		);

		$vars['roles'] = array(
			'administrator' => __('global.administrator'),
			'editor' => __('global.editor'),
			'staff' => __('global.staff')
		);

		return View::create('staffs/edit', $vars)
			->partial('header', 'partials/header')
			->partial('footer', 'partials/footer');
	});

	Route::post('admin/staffs/edit/(:num)', function($id) {

		$user = Auth::user();

		if ($user->role == 'staff' && $user->id != $id) {

			Notify::warning(__('staffs.noroleedit'));

			return Response::redirect('admin/staffs/edit/' . $user->id);
		}

		$staff = Staff::find($id);

		if ($user->role == 'editor' && !in_array($staff->division, $user->roles)) {

			Notify::warning(__('staffs.noroleedit'));

			return Response::redirect('admin/staffs/');
		}


		$input = Input::get(array(
			'message',
			'rating',
			'salutation',
			'first_name',
			'last_name',
			'display_name',
			'gender',
			'email',
			'telephone',
			'fax',
			'status',
			'slug',
			'report_to',
			'scheme',
			'grade',
			'job_title',
			'position',
			'description',
			'division',
			'management',
		));

		$account_enable = false;
		$password_reset = false;
		$input['display_name'] = trim($input['display_name']);
		$input['management'] = $input['management'] ?: 0;

		if(empty($input['slug'])) {
			$input['slug'] = $input['email'] ?: $input['display_name'];
		}

		$input['slug'] = slug($input['slug']);

		if ($account = Input::get('account')) {
			$account_enable = true;
			$input['account'] = $account;
			$input['role'] = Input::get('role');
		} else {
			$input['role'] = 'staff';
		}

		if ($account_enable) {
			if($username = Input::get('username')) {
			$input['username'] = $username;
			}

			if($password = Input::get('password')) {
			$input['password'] = $password;
			$password_reset = true;
			}
		}

		$validator = new Validator($input);

		$validator->add('safe', function($str) use($id) {
			return ($str != 'inactive' and Auth::user()->id == $id);
		});

		$validator->check('email')
			->is_email(__('staffs.email_missing'));

		if (strtolower($input['telephone']) != 'n/a') {
			$validator->check('telephone')
				->is_max(4, __('staffs.telephone_missing', 4));
		}

		if($account_enable) {
			$validator->check('username')
				->is_max(2, __('staffs.username_missing', 2));
		}

		if($password_reset) {
			$validator->check('password')
				->is_max(6, __('staffs.password_too_short', 6));
		}

		if($errors = $validator->errors()) {
			Input::flash();

			Notify::warning($errors);

			return Response::redirect('admin/staffs/edit/' . $id);
		}

		if($password_reset) {
			$input['password'] = Hash::make($input['password']);
		}

		$hierarchy = array(
			'branch' => 0,
			'sector' => 0,
			'unit' => 0,
		);

		$input['report_to'] = ($reportTo = Input::get('report_to')) ? Staff::setid($reportTo) : 0;

		$input['personal_assistant'] = ($pa = Input::get('personal_assistant')) ? Staff::setid($pa) : 0;

		foreach (array('branch', 'sector', 'unit') as $org) {

			if ($orgid = Input::get($org)) {
				$input[$org] = $org::id($orgid);
				$hierarchy[$org] = $input[$org];
			} else {
				$input[$org] = $hierarchy[$org] = 0;
			}

		}

		// set default credential for non administrator
		if ($user->role != 'administrator') {
			$input['account'] = $staff->account;
			$input['role'] = $staff->role;
		}

		$input['updated'] = Date::mysql('now');

		$diff = array_diff_assoc($staff->data, $input);

		$filteredRev = array_diff_key( $diff, array_flip( ['id', 'password', 'view', 'created', 'updated', 'last_visit', 'jusa', 'sort'] ) );

		if ($filteredRev) {
			// Before update, insert to revision
			if (Config::meta('revision')) {

				$revision = [];
				foreach($staff->data as $key => $value) {
					if ($key == 'id') {
						$revision['staff_id'] = $value;
					} else {
						$revision[$key] = $value;
					}
				}

				$extend = [];

				foreach (['avatar', 'twitter', 'facebook', 'gplus', 'github', 'display_avatar'] as $fields) {
					if($field = Extend::field('staff', $fields, $id)) {
						$extend[$fields] = Extend::value($field);
					}
				}

				$revision['extend'] = serialize($extend);
				$revision['admin'] = $user->id;
				$revision['revision_date'] = Date::mysql('now');

				//
				if (Revision::total($staff->id) > (int) Config::meta('max_revision')) {
					Revision::remove($staff->id);
				}

				Revision::create($revision);

			}

		}

		$filteredUpdate = array_diff_key( $diff, array_flip( ['id', 'view', 'created', 'updated', 'last_visit'] ) );


		if ($filteredUpdate) {
			Staff::update($id, $input);
			Extend::process('staff', $id, $input['email']);

			if($tags = Input::get('tag')) {

				Tag::process($id, $tags);

			}

		}

		if ($division = $input['division']) {
			$hierarchy['staff'] = $id;
			$hierarchy['division'] = $division;

			// hierarchy
			if ($exist = Hierarchy::where('staff', '=', $id)->fetch()) {
				Hierarchy::update($exist->id, $hierarchy);
			} else {
				Hierarchy::create($hierarchy);
			}

			Division::counter();

		}

		// division roles
		if($inputroles = Input::get('roles') ?: 0) {

			$roles = array();
			Role::where('staff', '=', $id)->delete();

			foreach ($inputroles as $div) {
				$roles['staff'] = $id;
				$roles['division'] = $div;
				Role::create($roles);
			}
		}

		if (!$inputroles) {
			Role::where('staff', '=', $id)->delete();
		}

		// Send email notification for editor
		if ($staff->role != 'editor' && $input['role'] == 'editor') {

			$email_div = array_map(function($var) {

				return ($item = Division::find($var)) ? $item->title : $var;

			}, $inputroles);

			// name, email, message, staff, ip, created, to, subject
			$emailer = [
				'to' => $staff->email,
				'subject' => __('email.editor_subject'),
				'message' => Braces::compile(PATH . 'content/editor.html', [
					'title' => __('email.editor_subject'),
					'hi' => __('email.hi'),
					'message' => __('email.editor_message', implode(', ', $email_div), Uri::full('admin/amnesia/')),
					'thanks' => __('email.thanks'),
					'footer' => __('site.title'),
				])
			];

			$mail = new Email($emailer);

			if(!$mail->send()) {
				Notify::warning(__('users.msg_not_send', $mail->ErrorInfo));
			}

			Notify::success(__('users.recovery_sent'));

		}

		Notify::success(__('staffs.updated'));

		return Response::redirect('admin/staffs/edit/' . $id);
	});

	/*
		Add staff
	*/
	Route::get('admin/staffs/add', function() {

		$user = Auth::user();

		if ($user->role == 'staff') {

			Notify::warning(__('staffs.noroleadd'));

			return Response::redirect('admin/staffs/edit/' . $user->id);
		}

		$vars['messages'] = Notify::read();
		$vars['token'] = Csrf::token();

		// extended fields
		$vars['fields'] = Extend::fields('staff');

		foreach (array('Scheme', 'Division', 'Branch', 'Sector', 'Unit') as $hierarchy) {
			$vars[strtolower($hierarchy) . 's'] = $hierarchy::dropdown();
			$vars[strtolower($hierarchy) . 's'] = array_unshift_assoc($vars[strtolower($hierarchy) . 's'], '0', __('staffs.please_select'));
		}

		$vars['genders'] = array(
			'M' => __('staffs.male'),
			'F' => __('staffs.female')
		);

		$vars['statuses'] = array(
			'inactive' => __('global.inactive'),
			'active' => __('global.active')
		);

		$vars['roles'] = array(
			'administrator' => __('global.administrator'),
			'editor' => __('global.editor'),
			'staff' => __('global.staff')
		);

		return View::create('staffs/add', $vars)
			->partial('header', 'partials/header')
			->partial('footer', 'partials/footer');
	});

	Route::post('admin/staffs/add', function() {

		$input = Input::get(array(
			'message',
			'rating',
			'salutation',
			'first_name',
			'last_name',
			'slug',
			'display_name',
			'gender',
			'email',
			'telephone',
			'fax',
			'status',
			'report_to',
			'scheme',
			'grade',
			'job_title',
			'position',
			'description',
			'management',
			'division',
			'branch',
			'sector',
			'unit',
		));

		if(empty($input['slug'])) {
			$input['slug'] = $input['email'] ?: $input['display_name'];
		}

		$input['display_name'] = trim($input['display_name']);
		$input['management'] = $input['management'] ?: 0;

		$input['slug'] = slug(trim($input['slug']));

		$account_enable = false;
		$password_reset = false;

		if ($account = Input::get('account')) {
			$input['account'] = $account;
			$account_enable = true;
		} else {
			unset($input['role']);
		}

		if($role = Input::get('role')) {
			$input['role'] = $role;
		}

		if ($account_enable) {

			if($username = Input::get('username')) {
				$input['username'] = $username;
			}

			if($password = Input::get('password')) {
				$input['password'] = $password;
				$password_reset = true;
			}
		}

			$validator = new Validator($input);

		if ($account_enable) {

			$validator->check('username')
				->is_max(2, __('staffs.username_missing', 2));

			$validator->check('password')
				->is_max(6, __('staffs.password_too_short', 6));

			$input['password'] = Hash::make($input['password']);

		}

			$validator->check('email')
				->is_email(__('staffs.email_missing'));

		$validator->check('telephone')
			->is_max(4, __('staffs.telephone_missing', 4));

			if($errors = $validator->errors()) {
				Input::flash();

				Notify::warning($errors);

				return Response::redirect('admin/staffs/add');
			}

		$hierarchy = array(
			'branch' => 0,
			'sector' => 0,
			'unit' => 0,
		);

		if ($reportTo = Input::get('report_to')) {
			$input['report_to'] = Staff::setid($reportTo);
		}

		if ($branch = Input::get('branch')) {
			$input['branch'] = Branch::id($branch);
			$hierarchy['branch'] = $input['branch'];
		}

		if ($sector = Input::get('sector')) {
			$input['sector'] = Sector::id($sector);
			$hierarchy['sector'] = $input['sector'];
		}

		if ($unit = Input::get('unit')) {
			$input['unit'] = Unit::id($unit);
			$hierarchy['unit'] = $input['unit'];
		}

		$input['created'] = Date::mysql('now');

		$staff = Staff::create($input);

		Extend::process('staff', $staff->id, $staff->email);

		if ($division = $input['division']) {
			$hierarchy['staff'] = $staff->id;
			$hierarchy['division'] = $division;
			Hierarchy::create($hierarchy);

			Division::counter();
		}

		// division roles
		if( $account_enable && $inputroles = Input::get('roles') ) {

			$roles = array();
			Role::where('staff', '=', $staff->id)->delete();

			foreach ($inputroles as $div) {
				$roles['staff'] = $staff->id;
				$roles['division'] = $div;
				Role::create($roles);

				//Role::update($role->id, $roles);
			}


		}

		// Automatically add 5 rating for new staff ;)
		Rating::create([
			'staff' => $staff->id,
			'score' => 5,
			'created' => $input['created']
		]);

		Notify::success(__('staffs.created', Uri::to('admin/staffs/edit/' . $staff->id)));

		$qs = '';
		if (Session::get('division')) {
			$qs = '/?division[]=' . Session::get('division');
		}

		return Response::redirect('admin/staffs' . $qs);
	});

	/*
		Delete staff
	*/
	Route::get('admin/staffs/delete/(:num)', function($id) {
		$self = Auth::user();

		if($self->id == $id) {
			Notify::warning(__('staffs.delete_error'));

			return Response::redirect('admin/staffs/edit/' . $id);
		}

		if ($staff = Staff::find($id)) {

			$revision = [];
			foreach($staff->data as $key => $value) {
				if ($key == 'id') {
					$revision['staff_id'] = $value;
				} else {
					$revision[$key] = $value;
				}
			}

			$extend = [];

			foreach (['avatar', 'twitter', 'facebook', 'gplus', 'github'] as $fields) {
				if($field = Extend::field('staff', $fields, $id)) {
					$extend[$fields] = Extend::value($field);
				}
			}

			$revision['extend'] = serialize($extend);
			$revision['admin'] = $self->id;
			$revision['status'] = 'delete';
			$revision['revision_date'] = Date::mysql('now');

			Revision::create($revision);

			Staff::where('id', '=', $id)->delete();
			Hierarchy::where('staff', '=', $id)->delete();
			Role::where('staff', '=', $id)->delete();

			//remove avatar
			$storage = PATH . 'content' . DS . 'avatar' . DS;
			$original = $storage . 'original' . DS;

			$avatar = preg_replace( "/^([^@]+)(@.*)$/", "$1", $staff->email) . '.jpg';
			$filepath = $storage . $avatar;

			if (file_exists($filepath)) {
				$copy = $original . $avatar;

				if (copy($filepath, $copy)) {
					unlink($filepath);
				}
			}

			Notify::success(__('staffs.deleted'));
		}

		$qs = '';
		if (Session::get('division')) {
			$qs = '/?division[]=' . Session::get('division');
		}


		return Response::redirect('admin/staffs' . $qs);
	});
});

Route::collection(array('before' => 'auth'), function() {

	/*
		Ajax change roles
	*/
	Route::post('admin/staffs/role', function() {
		$self = Auth::user();

		if (!Auth::admin()) {
			return Response::create(Json::encode(array('You are not allowed to do that!')), 200, array('content-type' => 'application/json'));
		}

		$input = filter_var_array(Input::get(array('id', 'role')), array(
				'id' => FILTER_SANITIZE_NUMBER_INT,
				'role' => FILTER_SANITIZE_STRING
			)
		);

		if (!$staff = Staff::find($input['id'])) {
			return Response::create(Json::encode(array('Staff not exist!')), 200, array('content-type' => 'application/json'));
		}

		Staff::update($staff->id, array('role' => $input['role'], 'updated' => Date::mysql('now')));

		return Response::create(Json::encode($input), 200, array('content-type' => 'application/json'));
	});


});

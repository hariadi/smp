<?php
set_time_limit(0);

use Hariadi\Siapa as Siapa;

Route::collection(array('before' => 'auth'), function() {
    /*
    Admin JSON API
    */
    Route::get(array('admin/import', 'admin/import/(:num)'), function($page = 1) {
    //Route::get('admin/import', function() {

        $loop = round(Migrate::count()/100);

        $migrates = Migrate::paginate($page, 100);
        foreach ($migrates->results as $staff) {

            $s = new Siapa($staff->nama);
            $s->setMiddle('b.');
            $input = array(
                'id' => $staff->id,
                'slug' => slug($s->givenName()),
                'salutation' => $s->salutation(),
                'display_name' => $s->givenName(true),
                'first_name' => $s->first(),
                'last_name' => $s->last(),
                'gender' => $s->gender(),
                'job_title' => $staff->jawatan,
                'position' => $staff->gelaran,
                'management' => (filter_var($staff->atasan, FILTER_VALIDATE_BOOLEAN)) ? 1 : 0,
                'jusa' => jusa($staff->gred),
                'description' => reportTo($staff->tugas),
                'report_to' => Migrate::parser(reportTo($staff->tugas)),
                'scheme' => $staff->skim,
                'grade' => $staff->gred,
                'division' => $staff->bahID,
                'branch' => Branch::id($staff->cawangan),
                'sector' => Sector::id($staff->seksyen),
                'unit' => Unit::id($staff->unit),
                'email' => $staff->emel,
                'telephone' => $staff->telefon,
                'status' => (filter_var($staff->status, FILTER_VALIDATE_BOOLEAN)) ? 'active' : 'inactive',
                'role' => 'staff',
                'view' => $staff->kaunter,
                'created' => Date::mysql('now'),
                );

            $input = array_filter($input);


            $staff = Staff::create($input);
            Extend::process('staff', $staff->id, $staff->email);

            $hierarchy = array(
                'staff' => $staff->id
            );

            if (isset($input['division'])) {
                $hierarchy['division'] = $input['division'];
            }

            if (isset($input['branch'])) {
                $hierarchy['branch'] = $input['branch'];
            }

            if (isset($input['sector'])) {
                $hierarchy['sector'] = $input['sector'];
            }

            if (isset($input['unit'])) {
                $hierarchy['unit'] = $input['unit'];
            }

            Hierarchy::create($hierarchy);
            Division::counter();

        }


        if ($page < $loop) {
            sleep(1);
            return Response::redirect('admin/import/' . ($page+1) );
        }

        //exit();
        $json = Json::encode($input);
        return Response::create($json, 200, array('content-type' => 'application/json'));

    });

});

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ps_endpoints;
use App\ps_aors;
use App\ps_auth;
use App\Dialplan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EndpointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $ps_endpoints = DB::table('ps_endpoints')
        ->orderBy('ps_endpoints.company', 'asc')
        ->orderBy('ps_endpoints.id','asc')
        ->leftJoin('ps_auths','ps_endpoints.id','=','ps_auths.id')
        ->leftJoin('ps_aors','ps_endpoints.id','=', 'ps_aors.id')
        ->leftJoin('ps_contacts','ps_endpoints.id','=', 'ps_contacts.endpoint')
        -> select('ps_endpoints.*','ps_auths.username','ps_auths.password','ps_aors.max_contacts','ps_aors.remove_existing','ps_contacts.uri','ps_contacts.user_agent')
        -> get();

        return view('endpoints.index',compact('ps_endpoints'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('endpoints.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'ext_number'=>'required',
            'context'=>'required',
            'company'=>'required',
            'password' => 'required',
            'pickup_group' => 'required'
        ]);
        
        $ext_company = $request->get('ext_number')."_".$request->get('company');

        $endpoint = new ps_endpoints([
    
            'id' => $ext_company,
            'transport' => "transport-udp",
            'aors' => $ext_company,
            'auth' => $ext_company, 
            'context' => $request->get('context'),
            'company' => $request->get('company'),
            'disallow' => "all",
            'allow' => "g722,ulaw",
            'direct_media' => "no",
            'force_rport' => "yes",
            'rewrite_contact' => "yes",
            'rtp_symmetric' => "yes",
            'call_group' => "1",
            'pickup_group' => $request->get('pickup_group')
        ]);

        $auth = new ps_auth([
            'id' => $ext_company,
            'auth_type' => "userpass",
            'username' => $ext_company,
            'password' => $request->get('password')
        ]);
        
        $aors = new ps_aors([
            'id' => $ext_company,
            'max_contacts' => "1",
            'remove_existing' => "yes",
        ]);

        $dialplan = new Dialplan([
            'ext_number' => $ext_company,
            'company' => $request->get('company'),
            'technology' => 'PJSIP',
            'dialstring1' => $ext_company,
            'context' => $request->get('context'),
        ]);
        
        $dialplan2 = new Dialplan([
            'ext_number' => $request->get('ext_number'),
            'company' => $request->get('company'),
            'technology' => 'PJSIP',
            'dialstring1' => $ext_company,
            'context' => $request->get('context'),
        ]);
        

        $endpoint->save();
        $auth->save();
        $aors->save();
        $dialplan->save();
        $dialplan2->save();

        return redirect('/endpoints')->with('success', 'Contact saved!');
        
        /* commented due to extensions.d file saving not used anymore
        $contents = "Contents\n";
        $contents .= "two";
        Storage::disk('local')->put('/extensions.d/file.txt', $contents);

        print ("<html>Saved!</html>");
        */
    }

    /**
     * Display the specified resource by context
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $endpoint = ps_endpoints::find($id);
        $id_array=preg_split("/_/",$endpoint->id);

        $ext_number = $id_array[0];
        $company = $id_array[1];

        $auth = ps_auth::find($id);

        $dialplan = Dialplan::where('ext_number',$ext_number)->first();

        return view('endpoints.edit')
            ->with (compact('endpoint'))
            ->with (compact('auth')) 
            ->with (compact('ext_number'))
            ->with (compact('company'))
            ->with (compact('dialplan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => 'required',
            'dialled' => 'required',
            'pickup_group' => 'required'
        ]);

        $ext_company = $request->get('ext_number')."_".$request->get('company');
        $ext_number = $request->get('ext_number').

        $endpoint = ps_endpoints::find($id);
        $auth = ps_auth::find($id);
        $dialplan = Dialplan::where('ext_number',$request->get('dialled'))->first();

        $endpoint->pickup_group = $request->get('pickup_group');
        $endpoint->save();

        $auth->password = $request->get('password');
        $auth->save();
      
        $dialplan->ext_number = $request->get('dialled');
        $dialplan->save();

        print "ID is ".$request->get('id');
        print "Context is ".$request->get('context');

        return redirect('endpoints')->with('success','Contact Edited!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $endpoint = ps_endpoints::find($id);
        $auth = ps_auth::find($id);
        $aors = ps_aors::find($id);
        $dialplan= Dialplan::where('dialstring1', $id)->delete();

        $endpoint->delete();
        $auth->delete();
        $aors->delete();
        
        return redirect('/endpoints')->with('success', 'Contact deleted!');
    }
}

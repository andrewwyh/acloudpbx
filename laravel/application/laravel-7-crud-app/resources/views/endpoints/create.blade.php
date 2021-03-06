@extends('layouts.app')

@section('content')
<div class="row">
 <div class="col-sm-8 offset-sm-2">
    <h1 class="display-3">Add an endpoint</h1>
  <div>
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
      </div><br />
    @endif
      <form method="post" action="{{ route('endpoints.store') }}">
          @csrf
          <div class="form-group">    
              <label for="ext_number">Extension number</label>
              <input type="text" class="form-control" name="ext_number"/>
          </div>

          <div class="form-group">
              <label for="company">Company</label>
              <input type="text" class="form-control" name="company"/>
          </div>

          <div class="form-group">
              <label for="password">Password</label>
              <input type="text" class="form-control" name="password"/>
          </div>
          <div class="form-group">
              <label for="pickup_group">Pickup Group</label>
              <input type="text" class="form-control" name="pickup_group"/>
          </div>

          <div class="form-group">
              <label for="context">Context</label>
              <input type="text" class="form-control" name="context" value="pbx-context"/>
          </div>
                               
          <button type="submit" class="btn btn-primary">Add Endpoint</button>
      </form>
  </div>
</div>
</div>
@endsection
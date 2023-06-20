@extends('servers.layout')
 
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-right">
                <x-primary-button><a class="btn btn-success" href="{{ route('servers.create') }}"> Create New Server</a></x-primary-button>
            </div>
        </div>
    </div>
   
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
   
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($servers as $server)
        <tr>
            <td>{{ $server->id }}</td>
            <td>{{ $server->name }}</td>
            <td>{{ $server->description }}</td>
            <td>
                <form action="{{ route('servers.destroy',$server->id) }}" method="POST">
   
                    <x-primary-button><a class="btn btn-info" href="{{ route('servers.show',$server->id) }}">Details</a></x-primary-button>
    
                    @csrf
                    @method('DELETE')
      
                    <button type="submit" class="btn btn-danger"><x-primary-button>Delete</x-primary-button></button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
  
    {!! $servers->links() !!}
      
@endsection
@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                <h4> Lorem ipsum dolor sit amet consectetur adipisicing elit. Debitis alias ipsam possimus soluta beatae
                    sequi nisi doloremque suscipit totam consectetur deserunt accusantium minima ducimus in reprehenderit
                    iure vel rem magni, consequatur sunt. Exercitationem itaque, vel, dolore quo sint error incidunt
                    mollitia provident officiis delectus tempora rem obcaecati perspiciatis consequuntur doloremque quisquam
                    labore natus id eveniet corporis consectetur, non omnis. Ipsum velit ducimus explicabo aliquam illo
                    sint. Commodi soluta cumque voluptatibus consequuntur cupiditate quam hic doloremque nisi minus qui
                    adipisci praesentium neque culpa, quis eligendi ducimus recusandae esse ex maiores! Veniam vel
                    perspiciatis pariatur dignissimos totam dolorum recusandae. Cupiditate magnam laboriosam dicta, at dolor
                    illo aliquid aliquam unde similique laborum sequi dignissimos ea aperiam eius, voluptatem amet. Ex,
                    ratione itaque earum non minima magnam accusamus omnis perspiciatis magni sint obcaecati ullam dolores
                    molestiae repellat. Vel distinctio a, minus excepturi delectus vitae ipsam, deserunt quos soluta,
                    aliquam in molestias nam saepe. Voluptate.</h4>

                <a href="{{ url()->previous() ?? route('admin.dashboard') }}" class="btn btn-sm btn-primary"> Go back</a>

            </div>
        </div>
    </div>
@endsection
@section('push_script')
@endsection

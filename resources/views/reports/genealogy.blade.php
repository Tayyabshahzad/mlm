@extends('demo.layout.app')
@section('custom_css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.3.9/go.js"></script>
    <style>
        #diagramDiv {
            width: 100%;
            height: 1000px; 
        }
    </style>
@endsection
@section('title', 'Product')
@section('content')
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Subheader-->
        <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
            <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex align-items-center flex-wrap mr-1">
                    <!--begin::Mobile Toggle-->
                    <button class="burger-icon burger-icon-left mr-4 d-inline-block d-lg-none"
                        id="kt_subheader_mobile_toggle">
                        <span></span>
                    </button>
                    <div class="d-flex align-items-baseline flex-wrap mr-5">
                        <h5 class="text-dark font-weight-bold my-1 mr-5"> Report </h5>
                        <!--end::Page Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="" class="text-muted">Reports</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="" class="text-muted">Genealogy Report</a>
                            </li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page Heading-->
                </div>
            </div>
        </div>
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <!--begin::Entry-->
            <div class="d-flex flex-column-fluid">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!--begin::Page Layout-->
                    <div class="d-flex flex-row">
                        <div class="flex-row-fluid ml-lg-8">
                            <div class="card card-custom gutter-b">
                                <div class="card-body p-0">
                                    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
                                        <div class="col-lg-12 text-center">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <form action="{{ route('report.genealogy.tree') }}" method="get">
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    class="text-left font-weight-bold text-muted text-uppercase">
                                                                    <div class="form-group rounded-0">
                                                                        <select name="id" id=""
                                                                            class="form-control rounded-0" required>
                                                                            <option value="" disabled selected> Select
                                                                                User</option>
                                                                            @foreach ($users as $user)
                                                                                <option value="{{ $user->id }}">
                                                                                    {{ $user->name }} </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </th>
                                                                <th
                                                                    class="text-left font-weight-bold text-muted text-uppercase">
                                                                    <div class="form-group">
                                                                        <button class="btn btn-sm btn-info rounded-0 "> Generate
                                                                            Report </button>
                                                                    </div>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                    </form>
                                                </table> 
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div id="diagramDiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Layout-->
                    </div>
                    <!--end::Page Layout-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Entry-->
        </div>
    </div>
@endsection
@section('page_js')

    <script>
        $(document).ready(function() {
            $('[data-target="#changeStatus"]').click(function() {
                var id = $(this).data('id');
                $('#member_id').val(id);
                $('#statusForm').attr('action', '/team/members/status/' + id + '/update');
            });
        });
    </script>
    <script>
        function init() {
            const $ = go.GraphObject.make;
            const myDiagram = $(go.Diagram, "diagramDiv", {
                layout: $(go.TreeLayout, {
                    angle: 90,
                    layerSpacing: 20
                }),
            });

            myDiagram.nodeTemplate = $(go.Node, "Auto",
                $(go.Shape, "RoundedRectangle", {
                    fill: "white",
                    strokeWidth: 2,
                    stroke: "#00d9ff"
                }),
                $(go.Panel, "Table", {
                        margin: 8
                    },
                    $(go.Picture, {
                            row: 0,
                            alignment: go.Spot.Center,
                            width: 50,
                            height: 50
                        },
                        new go.Binding("source", "image") // Bind the source property to the "image" field
                    ),
                    $(go.TextBlock, {
                            row: 1,
                            margin: 4,
                            font: "bold 12px sans-serif",
                            stroke: "#333"
                        },
                        new go.Binding("text", "name")),
                    ///// new Username Added
                    $(go.TextBlock, {
                            row: 2,
                            margin: 5,
                            font: "bold 10px sans-serif",
                            stroke: "#333"
                        },
                        new go.Binding("text", "username"))
                )
            );

            myDiagram.linkTemplate = $(go.Link, {
                    routing: go.Link.Orthogonal,
                    corner: 5
                },
                $(go.Shape, {
                    strokeWidth: 2,
                    stroke: "#00d9ff"
                })
            );

            // Use the nodeDataArray passed from the controller
            const nodeDataArray = @json($nodeDataArray);

            myDiagram.model = new go.TreeModel(nodeDataArray);
            const searchInput = document.getElementById("searchInput");
            searchInput.addEventListener("input", function() {
                const query = searchInput.value.toLowerCase();
                myDiagram.startTransaction("highlight search");
                myDiagram.nodes.each(node => {
                    node.isHighlighted = false;
                });
                if (query) {
                    myDiagram.nodes.each(node => {
                        const data = node.data;
                        const name = data.name ? data.name.toLowerCase() : "";
                        const username = data.username ? data.username.toLowerCase() : "";
                        if (name.includes(query) || username.includes(query)) {
                            node.isHighlighted = true;
                            myDiagram.centerRect(node.actualBounds);
                        }
                    });
                }

                myDiagram.commitTransaction("highlight search");
            });
        }

        init();
    </script>

@endsection

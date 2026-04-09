@extends('demo.layout.app')
@section('title','Saving Account Tree')
@section('custom_css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.3.9/go.js"></script>
    <style>
        #diagramDiv {
            width: 100%;
            height: 650px;
            border: 1px solid lightgray;
        }
    </style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Saving Account Tree</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Genealogy</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Saving Account Tree</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container-fluid">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Saving Account Genealogy Tree
                            <small class="text-muted">Root: {{ $root->username }}</small>
                        </h3>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="search-container mb-3">
                        <input type="text" id="searchInput" class="form-control rounded-0" placeholder="Search by name or username...">
                    </div>
                    <div id="diagramDiv"></div>
                    <p class="text-muted mt-2 font-size-sm">Total saving members: {{ count($nodeDataArray) - 1 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
    function init() {
        const $ = go.GraphObject.make;
        const myDiagram = $(go.Diagram, "diagramDiv", {
            layout: $(go.TreeLayout, { angle: 90, layerSpacing: 35 }),
        });

        myDiagram.nodeTemplate = $(go.Node, "Auto",
            $(go.Shape, "RoundedRectangle", { fill: "white", strokeWidth: 2, stroke: "#28a745" }),
            $(go.Panel, "Table", { margin: 8 },
                $(go.Picture,
                    { row: 0, alignment: go.Spot.Center, width: 50, height: 50 },
                    new go.Binding("source", "image")
                ),
                $(go.TextBlock, { row: 1, margin: 4, font: "bold 12px sans-serif", stroke: "#333" },
                    new go.Binding("text", "name")),
                $(go.TextBlock, { row: 2, margin: 5, font: "bold 10px sans-serif", stroke: "#555" },
                    new go.Binding("text", "username"))
            )
        );

        myDiagram.linkTemplate = $(go.Link,
            { routing: go.Link.Orthogonal, corner: 5 },
            $(go.Shape, { strokeWidth: 2, stroke: "#28a745" })
        );

        const nodeDataArray = @json($nodeDataArray);
        myDiagram.model = new go.TreeModel(nodeDataArray);

        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("input", function () {
            const query = searchInput.value.toLowerCase();
            myDiagram.startTransaction("highlight search");
            myDiagram.nodes.each(node => { node.isHighlighted = false; });
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

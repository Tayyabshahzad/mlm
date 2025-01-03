<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genealogy Chart</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.3.9/go.js"></script> <!-- Include GoJS -->
    <style>
        #diagramDiv {
            width: 100%;
            height: 600px;
            border: 1px solid lightgray;
        }
    </style>
</head>
<body>
    <div class="col-lg-12">
        <div id="diagramDiv"></div>
        <script>
            function init() {
                const $ = go.GraphObject.make;

                const myDiagram = $(go.Diagram, "diagramDiv", {
                    layout: $(go.TreeLayout, { angle: 90, layerSpacing: 35 }),
                });

                myDiagram.nodeTemplate = $(
                    go.Node,
                    "Auto",
                    $(
                        go.Shape,
                        "RoundedRectangle",
                        { fill: "white", strokeWidth: 2, stroke: "#00d9ff" }
                    ),
                    $(
                        go.Panel,
                        "Table",
                        { margin: 8 },
                        $(go.Picture, "https://via.placeholder.com/50", {
                            row: 0,
                            alignment: go.Spot.Center,
                            width: 50,
                            height: 50,
                        }),
                        $(
                            go.TextBlock,
                            { row: 1, margin: 4, font: "bold 12px sans-serif", stroke: "#333" },
                            new go.Binding("text", "name")
                        )
                    )
                );

                myDiagram.linkTemplate = $(
                    go.Link,
                    { routing: go.Link.Orthogonal, corner: 5 },
                    $(go.Shape, { strokeWidth: 2, stroke: "#00d9ff" })
                );

                const nodeDataArray = [
                    { key: 0, name: "Root Parent" },
                    { key: 1, parent: 0, name: "Child 1" },
                    { key: 2, parent: 0, name: "Child 2" },
                    { key: 3, parent: 0, name: "Child 3" },
                    { key: 4, parent: 0, name: "Child 4" },
                    { key: 5, parent: 0, name: "Child 5" },
                    { key: 6, parent: 1, name: "Sub-Child 1" },
                    { key: 7, parent: 1, name: "Sub-Child 2" },
                    { key: 8, parent: 2, name: "Sub-Child 3" },
                    { key: 9, parent: 3, name: "Sub-Child 4" },
                ];

                myDiagram.model = new go.TreeModel(nodeDataArray);
            }

            init();
        </script>
    </div>
</body>
</html>

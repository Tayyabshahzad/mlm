@extends('demo.layout.app')
@section('title', 'User Team - ' . $user->username)
@section('custom_css')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        #teamChart {
            width: 100%;
            height: 700px;
            border: 1px solid #e1e5e9;
            background: #f8f9fa;
        }

        .node circle {
            fill: #fff;
            stroke: #007bff;
            stroke-width: 3px;
            cursor: pointer;
        }

        .node.blocked circle {
            stroke: #dc3545;
            fill: #ffe6e6;
        }

        .node text {
            font: 12px sans-serif;
            text-anchor: middle;
            pointer-events: none;
            fill: #333;
        }

        .username-text {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #e1e5e9;
            border-radius: 15px;
            padding: 5px 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-weight: 500;
            font-size: 13px;
        }

        .username-text.highlighted {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            border-color: #ff6b6b;
            box-shadow: 0 3px 8px rgba(255, 107, 107, 0.3);
            font-weight: bold;
        }

        .username-text:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 1);
            transition: all 0.2s ease;
        }

        .username-text.highlighted:hover {
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
            transform: translateY(-2px);
        }

        .link {
            fill: none;
            stroke: #007bff;
            stroke-width: 2px;
        }

        .node image {
            clip-path: circle(25px);
        }

        .tooltip {
            position: absolute;
            text-align: left;
            padding: 10px;
            font: 12px sans-serif;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: 0px;
            border-radius: 5px;
            pointer-events: none;
            opacity: 0;
            z-index: 1000;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 2px;
            padding: 10px;
            text-align: center;
            min-width: 150px;
            margin: 5px;
        }

        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }

        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .level-filter {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 120px;
        }

        .node.highlighted {
            filter: drop-shadow(0 0 8px #ff6b6b);
        }

        .node.dimmed {
            opacity: 0.2 !important;
        }

        .link.dimmed {
            opacity: 0.1 !important;
        }

        .controls {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e1e5e9;
        }

        .node.highlighted text {
            font-weight: bold;
            font-size: 14px;
        }

        .level-view-header {
            background: #ff6b6b;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            display: none;
        }
    </style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-4 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-2 d-flex align-items-center">
                <h5 class="mt-2 mb-2 mr-5 text-dark font-weight-bold">Team Structure - {{ $user->username }}</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('users.index') }}" class="text-muted">Users</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('user.info', $user->id) }}" class="text-muted">{{ $user->username }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-muted">Team</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('user.info', $user->id) }}" class="px-3 btn btn-default font-weight-bold btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to User Info
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Team Hierarchy Visualization</h3>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Statistics -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-number" id="totalMembers">{{ count($nodeDataArray) }}</div>
                            <div class="stat-label">Total Members</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="activeMembers">{{ count(array_filter($nodeDataArray, fn($item) => !$item['blocked'])) }}</div>
                            <div class="stat-label">Active Members</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="blockedMembers">{{ count(array_filter($nodeDataArray, fn($item) => $item['blocked'])) }}</div>
                            <div class="stat-label">Blocked Members</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="maxDepth">{{ !empty($nodeDataArray) ? max(array_column($nodeDataArray, 'level')) : 0 }}</div>
                            <div class="stat-label">Maximum Depth</div>
                        </div>
                        @foreach(range(1, 7) as $level)
                            @php $levelCount = count(array_filter($nodeDataArray, fn($item) => $item['level'] == $level)) @endphp
                            @if($levelCount > 0)
                            <div class="stat-card">
                                <div class="stat-number">{{ $levelCount }}</div>
                                <div class="stat-label">Level {{ $level }} Team</div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Controls -->
                    <div class="controls">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email or phone..." style="max-width: 300px;">
                        <select id="levelFilter" class="level-filter">
                            <option value="">All Levels</option>
                        </select>
                        <button id="clearFilter" class="btn btn-sm btn-outline-secondary">Clear Filter</button>
                        <button id="expandAll" class="btn btn-sm btn-primary">Expand All</button>
                        <button id="collapseAll" class="btn btn-sm btn-secondary">Collapse All</button>
                        <button id="resetZoom" class="btn btn-sm btn-info">Reset Zoom</button>
                        <button id="showData" class="btn btn-sm btn-warning">Debug Data</button>
                    </div>

                    @if(config('app.debug'))
                    <!-- Debug Information (only visible in debug mode) -->
                    <div class="mt-3 alert alert-info">
                        <strong>Debug Info:</strong>
                        Total records loaded: {{ count($nodeDataArray) }} |
                        User ID: {{ $user->id }} |
                        Raw data: <button class="btn btn-sm btn-link" onclick="console.log(@json($nodeDataArray))">Log to Console</button>
                    </div>
                    @endif

                    <!-- Level Filter Header -->
                    <div id="levelViewHeader" class="level-view-header">
                        <h4 id="levelHeaderText">Viewing Level X Team Members</h4>
                    </div>

                    <!-- Chart Container -->
                    <div id="teamChart"></div>

                    <!-- Tooltip -->
                    <div class="tooltip" id="tooltip"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($nodeDataArray);

    // Set up dimensions and margins
    const margin = {top: 20, right: 120, bottom: 20, left: 120};
    const width = document.getElementById('teamChart').offsetWidth - margin.left - margin.right;
    const height = 700 - margin.top - margin.bottom;

    // Create SVG
    const svg = d3.select("#teamChart")
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom);

    const g = svg.append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // Create zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 3])
        .on("zoom", function(event) {
            g.attr("transform", event.transform);
        });

    svg.call(zoom);

    // Create tree layout
    const tree = d3.tree().size([height, width]);

    // Convert flat data to hierarchical structure
    function buildHierarchy(data) {
        const map = new Map();
        let root = null;

        // Create nodes map
        data.forEach(d => {
            map.set(d.id, {
                ...d,
                children: []
            });
        });

        // Build hierarchy
        data.forEach(d => {
            const node = map.get(d.id);
            if (d.parent === null || d.level === 0) {
                root = node;
            } else {
                const parent = map.get(d.parent);
                if (parent) {
                    parent.children.push(node);
                } else {
                    console.warn('Parent not found for node:', d);
                }
            }
        });

        // Debug output
        console.log('Raw data received:', data);
        console.log('Built hierarchy:', root);
        console.log('Total nodes in map:', map.size);

        // Debug levels
        const levelCounts = {};
        data.forEach(d => {
            levelCounts[d.level] = (levelCounts[d.level] || 0) + 1;
        });
        console.log('Level distribution:', levelCounts);

        return root;
    }

    // Create tooltip
    const tooltip = d3.select("#tooltip");

    // Initialize the tree
    const root = d3.hierarchy(buildHierarchy(data));

    let i = 0;

    function update(source) {
        // Compute the new tree layout
        const treeData = tree(root);
        const nodes = treeData.descendants();
        const links = treeData.descendants().slice(1);

        // Normalize for fixed-depth
        nodes.forEach(d => { d.y = d.depth * 180; });

        // Update the nodes
        const node = g.selectAll('g.node')
            .data(nodes, d => d.id || (d.id = ++i));

        // Enter any new nodes at the parent's previous position
        const nodeEnter = node.enter().append('g')
            .attr('class', 'node')
            .attr("transform", d => `translate(${source.y0 || 0},${source.x0 || 0})`)
            .on('click', click)
            .on('mouseover', function(event, d) {
                tooltip.transition()
                    .duration(200)
                    .style("opacity", .9);
                tooltip.html(`
                    <strong>${d.data.name}</strong><br/>
                    Email: ${d.data.email || 'N/A'}<br/>
                    Phone: ${d.data.phone || 'N/A'}<br/>
                    Level: ${d.data.level}<br/>
                    Status: ${d.data.blocked ? 'Blocked' : 'Active'}
                `)
                    .style("left", (event.pageX + 10) + "px")
                    .style("top", (event.pageY - 28) + "px");
            })
            .on('mouseout', function(d) {
                tooltip.transition()
                    .duration(500)
                    .style("opacity", 0);
            });

        // Add circles for the nodes
        nodeEnter.append('circle')
            .attr('class', d => d.data.blocked ? 'blocked' : '')
            .attr('r', 1e-6)
            .style("fill", d => d._children ? "lightsteelblue" : "#fff");

        // Add profile images
        nodeEnter.append('image')
            .attr('x', -25)
            .attr('y', -25)
            .attr('width', 50)
            .attr('height', 50)
            .attr('href', d => d.data.image)
            .style('clip-path', 'circle(25px)');

        // Add styled username labels using foreignObject
        nodeEnter.append('foreignObject')
            .attr('x', -60)
            .attr('y', 45)
            .attr('width', 120)
            .attr('height', 30)
            .append('xhtml:div')
            .style('text-align', 'center')
            .append('span')
            .attr('class', 'username-text')
            .text(d => d.data.name);

        // Transition nodes to their new position
        const nodeUpdate = nodeEnter.merge(node);

        nodeUpdate.transition()
            .duration(750)
            .attr("transform", d => `translate(${d.y},${d.x})`);

        // Update the node attributes and style
        nodeUpdate.select('circle')
            .attr('r', 30)
            .style("fill", d => d._children ? "lightsteelblue" : "#fff")
            .attr('cursor', 'pointer');

        // Remove any exiting nodes
        const nodeExit = node.exit().transition()
            .duration(750)
            .attr("transform", d => `translate(${source.y},${source.x})`)
            .remove();

        nodeExit.select('circle')
            .attr('r', 1e-6);

        nodeExit.select('text')
            .style('fill-opacity', 1e-6);

        // Update the links
        const link = g.selectAll('path.link')
            .data(links, d => d.id);

        // Enter any new links at the parent's previous position
        const linkEnter = link.enter().insert('path', "g")
            .attr("class", "link")
            .attr('d', d => {
                const o = {x: source.x0 || 0, y: source.y0 || 0};
                return diagonal(o, o);
            });

        // Transition links to their new position
        linkEnter.merge(link).transition()
            .duration(750)
            .attr('d', d => diagonal(d, d.parent));

        // Remove any exiting links
        link.exit().transition()
            .duration(750)
            .attr('d', d => {
                const o = {x: source.x, y: source.y};
                return diagonal(o, o);
            })
            .remove();

        // Store the old positions for transition
        nodes.forEach(d => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    // Click handler for expand/collapse
    function click(event, d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else {
            d.children = d._children;
            d._children = null;
        }
        update(d);
    }

    // Create diagonal path
    function diagonal(s, d) {
        const path = `M ${s.y} ${s.x}
                C ${(s.y + d.y) / 2} ${s.x},
                  ${(s.y + d.y) / 2} ${d.x},
                  ${d.y} ${d.x}`;
        return path;
    }

    // Initialize
    root.x0 = height / 2;
    root.y0 = 0;

    // Collapse after the second level (but keep more levels visible for debugging)
    function collapse(d) {
        if (d.children && d.depth > 1) { // Only collapse after level 1 instead of 0
            d._children = d.children;
            d._children.forEach(collapse);
            d.children = null;
        }
    }

    // Apply collapse function only to deeper levels
    if (root.children) {
        root.children.forEach(collapse);
    }

    update(root);

    // Update statistics
    function updateStats() {
        const totalMembers = data.length;
        const activeMembers = data.filter(d => !d.blocked).length;
        const blockedMembers = data.filter(d => d.blocked).length;
        const maxDepth = Math.max(...data.map(d => d.level));

        document.getElementById('totalMembers').textContent = totalMembers;
        document.getElementById('activeMembers').textContent = activeMembers;
        document.getElementById('blockedMembers').textContent = blockedMembers;
        document.getElementById('maxDepth').textContent = maxDepth;

        // Populate level filter
        const levelFilter = document.getElementById('levelFilter');
        levelFilter.innerHTML = '<option value="">All Levels</option>'; // Clear existing options

        const uniqueLevels = [...new Set(data.map(d => d.level))].sort((a, b) => a - b);
        uniqueLevels.forEach(level => {
            // Only show levels 1-7 in dropdown (skip level 0 which is the root user)
            if (level > 0) {
                const option = document.createElement('option');
                option.value = level;
                option.textContent = `Level ${level}`;
                levelFilter.appendChild(option);
            }
        });
    }

    updateStats();

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        g.selectAll('.node').style('opacity', function(d) {
            if (!searchTerm) return 1;

            const matchesName = d.data.name.toLowerCase().includes(searchTerm);
            const matchesEmail = d.data.email && d.data.email.toLowerCase().includes(searchTerm);
            const matchesPhone = d.data.phone && d.data.phone.includes(searchTerm);

            return (matchesName || matchesEmail || matchesPhone) ? 1 : 0.3;
        });
    });

    // Level filter
    document.getElementById('levelFilter').addEventListener('change', function(e) {
        const selectedLevel = e.target.value;

        console.log('Level filter changed to:', selectedLevel);

        // If a specific level is selected, expand all nodes first to ensure visibility
        if (selectedLevel && selectedLevel !== '') {
            const levelInt = parseInt(selectedLevel);

            // Expand all nodes to make sure we can see the selected level
            function expandAll(d) {
                if (d._children) {
                    d.children = d._children;
                    d._children = null;
                }
                if (d.children) {
                    d.children.forEach(expandAll);
                }
            }
            expandAll(root);
            update(root);

            // Wait a bit for the update to complete, then apply filtering
            setTimeout(() => {
                applyLevelFilter(levelInt);
            }, 100);
        } else {
            // Reset view
            clearAllFilters();
        }
    });

    function applyLevelFilter(levelInt) {
        console.log('Applying filter for level:', levelInt);

        let nodesAtLevel = 0;
        let visibleNodes = [];

        // Hide ALL nodes first
        g.selectAll('.node').style('display', 'none');
        g.selectAll('.link').style('display', 'none');

        // First pass: collect all visible nodes
        g.selectAll('.node').each(function(d) {
            if (d.data.level === levelInt) {
                visibleNodes.push(d);
            }
        });

        console.log('Total nodes at level', levelInt, ':', visibleNodes.length);

        // Calculate proper spacing and layout
        const nodeWidth = 140; // Approximate width needed per node (including styled text)
        const minSpacing = 170; // Minimum space between nodes (increased for better readability)
        const availableWidth = width - 100; // Leave margins
        const totalNodesWidth = visibleNodes.length * nodeWidth;
        const totalSpacingNeeded = (visibleNodes.length - 1) * minSpacing;

        let useGrid = false;
        let cols = 1;
        let rows = 1;

        // If nodes don't fit horizontally, use grid layout
        if (totalNodesWidth + totalSpacingNeeded > availableWidth && visibleNodes.length > 4) {
            useGrid = true;
            cols = Math.ceil(Math.sqrt(visibleNodes.length));
            rows = Math.ceil(visibleNodes.length / cols);
        }

        // Second pass: position and show nodes
        g.selectAll('.node').each(function(d, i) {
            const node = d3.select(this);

            if (d.data.level === levelInt) {
                nodesAtLevel++;

                // Show and highlight the node
                node.style('display', 'block')
                    .classed('highlighted', true)
                    .classed('dimmed', false)

                // Style the circle
                node.select('circle')
                    .style('stroke', '#ff6b6b')
                    .style('stroke-width', '4px')
                    .style('fill', '#fff');

                // Style the username text to be more readable
                node.select('.username-text')
                    .classed('highlighted', true);

                // Store original position if not already stored
                if (!d.originalTransform) {
                    d.originalTransform = node.attr('transform');
                }

                let xPos, yPos;

                if (useGrid) {
                    // Grid layout for many nodes
                    const currentIndex = nodesAtLevel - 1;
                    const col = currentIndex % cols;
                    const row = Math.floor(currentIndex / cols);

                    const gridSpacingX = Math.max(minSpacing, availableWidth / cols);
                    const gridSpacingY = 140; // Vertical spacing between rows (increased for username styling)

                    xPos = 50 + (col * gridSpacingX);
                    yPos = 100 + (row * gridSpacingY);
                } else {
                    // Horizontal layout for fewer nodes
                    const actualSpacing = Math.max(minSpacing, availableWidth / Math.max(visibleNodes.length, 1));
                    xPos = 50 + ((nodesAtLevel - 1) * actualSpacing);
                    yPos = height / 2;
                }

                // Animate to new position
                node.transition()
                    .duration(500)
                    .attr('transform', `translate(${xPos}, ${yPos})`);
            }
        });

        console.log('Layout used:', useGrid ? `Grid (${cols}x${rows})` : 'Horizontal');

        // Show level header
        document.getElementById('levelViewHeader').style.display = 'block';
        document.getElementById('levelHeaderText').textContent =
            `Viewing Level ${levelInt} Team Members (${visibleNodes.length} members)`;

        // Update statistics for filtered view
        updateFilteredStats(levelInt.toString());
    }

    function clearAllFilters() {
        // Hide level header
        document.getElementById('levelViewHeader').style.display = 'none';

        g.selectAll('.node').each(function(d) {
            const node = d3.select(this);

            // Restore original position if it was stored
            if (d.originalTransform) {
                node.transition()
                    .duration(500)
                    .attr('transform', d.originalTransform);
            }

            node.style('display', 'block')
                .classed('highlighted', false)
                .classed('dimmed', false)
                .style('opacity', 1);

            // Reset circle style
            node.select('circle')
                .style('stroke', d => d.data.blocked ? '#dc3545' : '#007bff')
                .style('stroke-width', '3px')
                .style('fill', '#fff');

            // Reset username text style
            node.select('.username-text')
                .classed('highlighted', false);
        });

        g.selectAll('.link')
            .style('display', 'block')
            .classed('dimmed', false)
            .style('opacity', 1)
            .style('stroke', '#007bff')
            .style('stroke-width', '2px');

        updateStats();
    }

    // Function to update statistics based on filter
    function updateFilteredStats(selectedLevel) {
        if (!selectedLevel || selectedLevel === '') {
            // Reset to original stats
            updateStats();
        } else {
            const filteredData = data.filter(d => d.level == parseInt(selectedLevel));
            const totalFiltered = filteredData.length;
            const activeFiltered = filteredData.filter(d => !d.blocked).length;
            const blockedFiltered = filteredData.filter(d => d.blocked).length;

            // Temporarily update stats display
            document.getElementById('totalMembers').textContent = `${totalFiltered} (Level ${selectedLevel})`;
            document.getElementById('activeMembers').textContent = activeFiltered;
            document.getElementById('blockedMembers').textContent = blockedFiltered;
        }
    }

    // Clear filter button
    document.getElementById('clearFilter').addEventListener('click', function() {
        document.getElementById('levelFilter').value = '';
        document.getElementById('searchInput').value = '';
        clearAllFilters();
    });

    // Control buttons
    document.getElementById('expandAll').addEventListener('click', function() {
        function expandAll(d) {
            if (d._children) {
                d.children = d._children;
                d._children = null;
            }
            if (d.children) {
                d.children.forEach(expandAll);
            }
        }
        expandAll(root);
        update(root);
    });

    document.getElementById('collapseAll').addEventListener('click', function() {
        root.children.forEach(collapse);
        update(root);
    });

    document.getElementById('resetZoom').addEventListener('click', function() {
        svg.transition().duration(750).call(
            zoom.transform,
            d3.zoomIdentity
        );
    });

    // Debug button
    document.getElementById('showData').addEventListener('click', function() {
        console.log('Raw Data:', data);
        console.log('Hierarchy Root:', root);
        console.log('Total nodes in hierarchy:', root.descendants().length);

        // Show data in a modal or alert
        const debugInfo = `
            Total Data Records: ${data.length}
            Hierarchy Nodes: ${root.descendants().length}
            Levels found: ${Array.from(new Set(data.map(d => d.level))).sort().join(', ')}

            Level breakdown:
            ${Array.from(new Set(data.map(d => d.level))).sort().map(level =>
                `Level ${level}: ${data.filter(d => d.level === level).length} members`
            ).join('\\n')}
        `;

        alert(debugInfo);
    });
});
</script>
@endsection
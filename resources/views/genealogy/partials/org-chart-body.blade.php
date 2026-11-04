{{--
    Org chart (pan / zoom / collapse, details panel on the right).
    Needs: $nodeDataArray (GenealogyController::orgNode rows). Optional: $ocTree ('standard'|'saving'), $ocTeamLabel.
--}}
<div class="card card-custom gutter-b">
    <div class="card-body py-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div class="font-size-sm text-muted my-1">
                            <span class="font-weight-bolder text-dark" id="ocTotal"></span> members in your {{ $ocTeamLabel ?? 'team' }} ·
                            click a member to see details
                        </div>
                        <div class="d-flex align-items-center my-1">
                            <input type="text" id="ocSearch" class="form-control form-control-sm mr-2" style="width:240px" placeholder="Search name or username…">
                            <button type="button" class="btn btn-sm btn-light-primary font-weight-bold mr-1" id="ocExpandAll">Expand all</button>
                            <button type="button" class="btn btn-sm btn-light font-weight-bold" id="ocCollapseAll">Collapse</button>
                        </div>
                    </div>

                    <div class="oc-wrap" id="ocWrap">
                        <div class="oc-stage" id="ocStage">
                            <svg class="oc-links" id="ocLinks"></svg>
                        </div>
                        <div class="oc-tools">
                            <button type="button" id="ocZoomIn" title="Zoom in">+</button>
                            <button type="button" id="ocZoomOut" title="Zoom out">&minus;</button>
                            <button type="button" id="ocFit" title="Fit to screen"><i class="fas fa-expand-arrows-alt" style="font-size:12px"></i></button>
                        </div>
                        <div class="oc-hint">Drag to move · scroll to zoom</div>
                    </div>
    </div>
</div>

{{-- Details panel (slides in from the right) --}}
<div class="oc-overlay" id="ocOverlay"></div>
<aside class="oc-panel" id="ocPanel" aria-hidden="true">
    <div class="oc-panel-head">
        <button type="button" class="oc-panel-close" id="ocClose" aria-label="Close">&times;</button>
        <div class="d-flex align-items-center">
            <div id="ocPAvatar"></div>
            <div class="ml-3" style="min-width:0">
                <div class="font-weight-bolder font-size-h5 text-truncate" id="ocPName"></div>
                <div style="opacity:.85" id="ocPUser"></div>
                <div class="mt-1" id="ocPTags"></div>
            </div>
        </div>
    </div>
    <div class="oc-panel-body" id="ocPBody"></div>
</aside>

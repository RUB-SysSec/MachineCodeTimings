<div class="col-sm-3 col-md-2 sidebar">
    <ul class="nav nav-sidebar">

        <li class="{{$include == "overview.blade.php" ? 'active' :''}}">
            <a href="/overview">Overview</a>
        </li>
        <li class="{{$include == "intrinsic_categories.blade.php" ? 'active' :''}}">
            <a href="/intrinsics/category/">Instructions</a>
        </li>
        <li class="{{$include == "intrinsicAdd.blade.php" ? 'active' :''}}">
            <a href="/intrinsics/add/">InstructionsAdd</a>
        </li>
    </ul>
    <hr>
    <ul class="nav nav-sidebar">
        <li class="{{$include == "compilations.blade.php" ? 'active' :''}}">
            <a href="/compilations/list">Compilations</a>
        </li>
             
    </ul>    
    <hr>
    <ul class="nav nav-sidebar">
        <li class="{{$include == "templates.blade.php" ? 'active' :''}}">
            <a href="/templates">Templates</a>
        </li>
             
    </ul>
    <hr>
    <ul class="nav nav-sidebar">
        <li class="{{$include == "parameterTypeInits.blade.php" ? 'active' :''}}">
            <a href="/parameterTypeInits/list">ParameterTypeInits</a>
        </li>       
        <li class="{{$include == "parameterTypeCategories.blade.php" ? 'active' :''}}">
            <a href="/parameterTypeInits/categories/list">ParameterTypeInitCategories</a>
        </li>             
    </ul>
    <hr>
    <ul class="nav nav-sidebar">
        <li class="{{$include == "node.blade.php" ? 'active' :''}}">
            <a href="/nodes/list">Nodes</a>
        </li>       
        <li class="{{$include == "nodeAdd.blade.php" ? 'active' :''}}">
            <a href="/nodes/add">NodesAdd</a>
        </li>             
    </ul>
    <hr>    
    <ul class="nav nav-sidebar">
        <li class="{{$include == "jobs.blade.php" ? 'active' :''}}">
            <a href="/jobs">Jobs</a>
        </li>    
        <li class="{{$include == "prepareJob.blade.php" ? 'active' :''}}">
            <a href="/ampq/prepareJob">Create Job</a>
        </li>
        <li class="{{$include == "queueStatus.blade.php" ? 'active' :''}}">
            <a href="/ampq/queueStatus">Queue Status</a>
        </li>                  
    </ul>
    <hr>
    <ul class="nav nav-sidebar">
        <li class="{{$include == "api.blade.php" ? 'active' :''}}">
            <a href="/api2">API</a>
        </li>       
    </ul>    
</div>

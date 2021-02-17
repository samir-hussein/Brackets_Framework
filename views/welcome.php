<?php extend('layouts/main') ?>
<?php session('title', 'Welcome') ?>

<?php startSession('content') ?>
<div class="d-flex flex-column align-items-center justify-content-center uk-background-muted" style="height:100vh">
    <h1>Welcome In MVC Framework</h1>
    <h2 class="m-0">Let's Start</h2>
    <hr class="uk-divider-small">
</div>
<?php endSession("content") ?>
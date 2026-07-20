<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card p-4">
            <h1 class="h3 text-center mb-2">Connexion</h1>
            <p class="text-secondary text-center">Client ou opérateur</p>
            <form method="post" action="<?= site_url('login') ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="credential">Numéro ou Clé</label>
                <input class="form-control form-control-lg" id="credential" name="credential"
                    value="<?= esc(old('credential')) ?>" inputmode="numeric" pattern="[0-9]{10}"
                    maxlength="10" required autofocus>
                <button class="btn btn-primary w-100 mt-3">Se connecter</button>
            </form>
            <br>
            <p>Clé</p>
            <ul>
                <li>0000000000</li>
                <li>1111111111</li>
                <li>2222222222</li>
            </ul>
        </div>
        <br>
        <p>Clé :</p>
        <ul>
            <li>0000000000</li>
            <li>1111111111</li>
            <li>2222222222</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>

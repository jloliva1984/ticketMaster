<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt-fill me-2 text-warning"></i><?= lang('General.quarries') ?></span>
        <button class="btn btn-primary btn-sm" id="btn-create">
            <i class="bi bi-plus-lg me-1"></i><?= lang('General.create') ?>
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt-quarries" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= lang('General.quarry') ?></th>
                        <th><?= lang('General.status') ?></th>
                        <th>Created</th>
                        <th><?= lang('General.actions') ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ── Modal Create / Edit ──────────────────────────────── -->
<div class="modal fade" id="modal-quarry" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-quarry" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-quarry-title"><?= lang('General.create') ?> <?= lang('General.quarry') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="quarry-id">
                    <div id="quarry-errors" class="alert alert-danger d-none mb-3 py-2"></div>

                    <div class="mb-3">
                        <label class="form-label">Nombre Cantera <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_cantera" id="quarry-nombre" class="form-control"
                               placeholder="Quarry name" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('General.status') ?></label>
                        <select name="is_active" id="quarry-active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('General.cancel') ?></button>
                    <button type="submit" class="btn btn-primary" id="btn-save-quarry">
                        <i class="bi bi-save me-1"></i><?= lang('General.save') ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>/';

// ── DataTable ────────────────────────────────────────────
const table = $('#dt-quarries').DataTable({
    ajax: { url: BASE + 'quarries/data', dataSrc: 'data' },
    columns: [
        { data: 'id',             width: '50px' },
        { data: 'nombre_cantera' },
        { data: 'is_active',      orderable: false },
        { data: 'created_at' },
        { data: 'actions',        orderable: false, width: '90px' },
    ],
    order: [[1, 'asc']],
});

// ── Modal helpers ────────────────────────────────────────
const modalEl  = document.getElementById('modal-quarry');
const modalObj = () => bootstrap.Modal.getOrCreateInstance(modalEl);
const titleEl  = document.getElementById('modal-quarry-title');
const errEl    = document.getElementById('quarry-errors');

function clearErrors() {
    errEl.classList.add('d-none');
    errEl.innerHTML = '';
}
function showErrors(errors) {
    errEl.innerHTML = '<ul class="mb-0 ps-3">' + Object.values(errors).map(e => `<li>${e}</li>`).join('') + '</ul>';
    errEl.classList.remove('d-none');
}

// Open Create
document.getElementById('btn-create').addEventListener('click', () => {
    document.getElementById('form-quarry').reset();
    document.getElementById('quarry-id').value = '';
    titleEl.textContent = '<?= lang('General.create') ?> <?= lang('General.quarry') ?>';
    clearErrors();
    modalObj().show();
});

// Open Edit
window.openEdit = async (id) => {
    clearErrors();
    try {
        const data = await tmAjax(BASE + 'quarries/' + id + '/edit');
        document.getElementById('quarry-id').value     = data.id;
        document.getElementById('quarry-nombre').value = data.nombre_cantera;
        document.getElementById('quarry-active').value = data.is_active;
        titleEl.textContent = 'Edit <?= lang('General.quarry') ?>';
        modalObj().show();
    } catch (err) { showToast(err.message, 'danger'); }
};

// Submit
document.getElementById('form-quarry').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    const formData = new FormData(e.target);
    const id       = formData.get('id');
    const url      = BASE + 'quarries' + (id ? '/' + id : '');
    const btn      = document.getElementById('btn-save-quarry');
    btn.disabled   = true;

    try {
        const res = await tmAjax(url, { method: 'POST', body: formData });
        showToast(res.message);
        modalObj().hide();
        table.ajax.reload();
    } catch (err) {
        if (err.errors) showErrors(err.errors);
        else showToast(err.message, 'danger');
    } finally { btn.disabled = false; }
});

// Delete
window.deleteQuarry = (id, name) => {
    if (!confirm(`Delete quarry "${name}"?`)) return;
    tmAjax(BASE + 'quarries/' + id, { method: 'DELETE' })
        .then(res => { showToast(res.message); table.ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};
</script>
<?= $this->endSection() ?>

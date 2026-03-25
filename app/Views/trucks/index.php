<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-truck-front-fill me-2 text-primary"></i><?= lang('General.trucks') ?></span>
        <button class="btn btn-primary btn-sm" id="btn-create">
            <i class="bi bi-plus-lg me-1"></i><?= lang('General.create') ?>
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt-trucks" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Camión</th>
                        <th>Nombre Chofer</th>
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
<div class="modal fade" id="modal-truck" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-truck" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-truck-title"><?= lang('General.create') ?> <?= lang('General.trucks') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="truck-id">
                    <div id="truck-errors" class="alert alert-danger d-none mb-3 py-2"></div>

                    <div class="mb-3">
                        <label class="form-label">No. Camión <span class="text-danger">*</span></label>
                        <input type="text" name="no_camion" id="truck-no" class="form-control"
                               placeholder="e.g. T-001" maxlength="50" required
                               style="text-transform:uppercase">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre Chofer <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_chofer" id="truck-chofer" class="form-control"
                               placeholder="Driver full name" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('General.status') ?></label>
                        <select name="is_active" id="truck-active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('General.cancel') ?></button>
                    <button type="submit" class="btn btn-primary" id="btn-save-truck">
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
const table = $('#dt-trucks').DataTable({
    ajax: { url: BASE + 'trucks/data', dataSrc: 'data' },
    columns: [
        { data: 'id',            width: '50px' },
        { data: 'no_camion' },
        { data: 'nombre_chofer' },
        { data: 'is_active',     orderable: false },
        { data: 'created_at' },
        { data: 'actions',       orderable: false, width: '90px' },
    ],
    order: [[1, 'asc']],
});

// ── Modal helpers ────────────────────────────────────────
const modalEl  = document.getElementById('modal-truck');
const modalObj = () => bootstrap.Modal.getOrCreateInstance(modalEl);
const titleEl  = document.getElementById('modal-truck-title');
const errEl    = document.getElementById('truck-errors');

function clearErrors() { errEl.classList.add('d-none'); errEl.innerHTML = ''; }
function showErrors(errors) {
    errEl.innerHTML = '<ul class="mb-0 ps-3">' + Object.values(errors).map(e => `<li>${e}</li>`).join('') + '</ul>';
    errEl.classList.remove('d-none');
}

// Open Create
document.getElementById('btn-create').addEventListener('click', () => {
    document.getElementById('form-truck').reset();
    document.getElementById('truck-id').value = '';
    titleEl.textContent = '<?= lang('General.create') ?> Truck';
    clearErrors();
    modalObj().show();
});

// Open Edit
window.openEdit = async (id) => {
    clearErrors();
    try {
        const data = await tmAjax(BASE + 'trucks/' + id + '/edit');
        document.getElementById('truck-id').value      = data.id;
        document.getElementById('truck-no').value      = data.no_camion;
        document.getElementById('truck-chofer').value  = data.nombre_chofer;
        document.getElementById('truck-active').value  = data.is_active;
        titleEl.textContent = 'Edit Truck';
        modalObj().show();
    } catch (err) { showToast(err.message, 'danger'); }
};

// Auto-uppercase truck number
document.getElementById('truck-no').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});

// Submit
document.getElementById('form-truck').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    const formData = new FormData(e.target);
    const id       = formData.get('id');
    const url      = BASE + 'trucks' + (id ? '/' + id : '');
    const btn      = document.getElementById('btn-save-truck');
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
window.deleteTruck = (id, no) => {
    if (!confirm(`Delete truck "${no}"?`)) return;
    tmAjax(BASE + 'trucks/' + id, { method: 'DELETE' })
        .then(res => { showToast(res.message); table.ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};
</script>
<?= $this->endSection() ?>

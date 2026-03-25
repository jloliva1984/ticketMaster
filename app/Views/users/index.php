<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people-fill me-2 text-primary"></i><?= lang('General.users') ?></span>
        <button class="btn btn-primary btn-sm" id="btn-create">
            <i class="bi bi-plus-lg me-1"></i><?= lang('General.create') ?>
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt-users" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= lang('General.users') ?></th>
                        <th><?= lang('General.email') ?></th>
                        <th>Role</th>
                        <th><?= lang('General.status') ?></th>
                        <th>Last Login</th>
                        <th><?= lang('General.actions') ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ── Modal Create / Edit ──────────────────────────────── -->
<div class="modal fade" id="modal-user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-user" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-user-title"><?= lang('General.create') ?> <?= lang('General.users') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="user-id">

                    <!-- Validation errors container -->
                    <div id="user-errors" class="alert alert-danger d-none mb-3 py-2"></div>

                    <div class="mb-3">
                        <label class="form-label"><?= lang('General.users') ?> Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="user-name" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('General.email') ?> <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="user-email" class="form-control" placeholder="user@example.com" required>
                    </div>
                    <div class="mb-3" id="password-group">
                        <label class="form-label">
                            <?= lang('General.password') ?>
                            <small class="text-muted fw-normal" id="pass-hint">(leave blank to keep current)</small>
                        </label>
                        <div style="position:relative">
                            <input type="password" name="password" id="user-password" class="form-control"
                                   placeholder="Min. 8 characters">
                            <button type="button" class="password-toggle" tabindex="-1" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" id="user-role" class="form-select" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?= lang('General.status') ?></label>
                            <select name="is_active" id="user-active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('General.cancel') ?></button>
                    <button type="submit" class="btn btn-primary" id="btn-save-user">
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
const table = $('#dt-users').DataTable({
    ajax: { url: BASE + 'users/data', dataSrc: 'data' },
    columns: [
        { data: 'id',         width: '50px' },
        { data: 'name' },
        { data: 'email' },
        { data: 'role',       orderable: false },
        { data: 'is_active',  orderable: false },
        { data: 'last_login' },
        { data: 'actions',    orderable: false, width: '90px' },
    ],
    order: [[0, 'desc']],
});

// ── Modal helpers ────────────────────────────────────────
const modalEl    = document.getElementById('modal-user');
const modalObj   = () => bootstrap.Modal.getOrCreateInstance(modalEl);
const titleEl    = document.getElementById('modal-user-title');
const errEl      = document.getElementById('user-errors');
const passHint   = document.getElementById('pass-hint');

function clearErrors() {
    errEl.classList.add('d-none');
    errEl.innerHTML = '';
    document.querySelectorAll('#form-user .is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function showErrors(errors) {
    const items = Object.values(errors).map(e => `<li>${e}</li>`).join('');
    errEl.innerHTML = `<ul class="mb-0 ps-3">${items}</ul>`;
    errEl.classList.remove('d-none');
}

// Open Create modal
document.getElementById('btn-create').addEventListener('click', () => {
    document.getElementById('form-user').reset();
    document.getElementById('user-id').value = '';
    titleEl.textContent = '<?= lang('General.create') ?> User';
    passHint.style.display = 'none'; // required on create
    document.getElementById('user-password').required = true;
    clearErrors();
    modalObj().show();
});

// Open Edit modal
window.openEdit = async (id) => {
    clearErrors();
    try {
        const data = await tmAjax(BASE + 'users/' + id + '/edit');
        document.getElementById('user-id').value        = data.id;
        document.getElementById('user-name').value      = data.name;
        document.getElementById('user-email').value     = data.email;
        document.getElementById('user-role').value      = data.role;
        document.getElementById('user-active').value    = data.is_active;
        document.getElementById('user-password').value  = '';
        document.getElementById('user-password').required = false;
        passHint.style.display = 'inline';
        titleEl.textContent = 'Edit User';
        modalObj().show();
    } catch (err) {
        showToast(err.message, 'danger');
    }
};

// Form submit
document.getElementById('form-user').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(e.target);
    const id       = formData.get('id');
    const url      = BASE + 'users' + (id ? '/' + id : '');
    const btn      = document.getElementById('btn-save-user');
    btn.disabled   = true;

    try {
        const res = await tmAjax(url, { method: 'POST', body: formData });
        showToast(res.message);
        modalObj().hide();
        table.ajax.reload();
    } catch (err) {
        if (err.errors) showErrors(err.errors);
        else showToast(err.message, 'danger');
    } finally {
        btn.disabled = false;
    }
});

// Delete
window.deleteUser = (id, name) => {
    if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
    tmAjax(BASE + 'users/' + id, { method: 'DELETE' })
        .then(res => { showToast(res.message); table.ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};

// Password toggle in modal
document.querySelector('#modal-user .password-toggle')?.addEventListener('click', function () {
    const inp = document.getElementById('user-password');
    inp.type  = inp.type === 'password' ? 'text' : 'password';
    this.querySelector('i').className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
<?= $this->endSection() ?>

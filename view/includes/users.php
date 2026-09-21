<!--end::Sidebar-->
<!--begin::App Main-->

<main class="app-main">
  <!--Header (Users    Home/Users)-->
  <div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
      <!--begin::Row-->
      <div class="row">
        <div class="col-sm-6">
          <h1 class="mb-0 fs-3">Users</h1>
        </div>
        <div class="col-sm-6">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ol>
          </nav>
        </div>
      </div>
      <!--end::Row-->
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">
      <!--begin::Row-->
      <div class="row">
        <div class="col-12">
          <!--begin::Card-->
          <div class="card mb-4">
            <!--begin::Card Header-->
            <div class="card-header">
              <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                  <h3 class="card-title">User Directory</h3>
                </div>
                <div class="col-12 col-md-8">
                  <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <div class="input-group input-group-sm w-auto">
                      <span class="input-group-text">
                        <i class="bi bi-search" aria-hidden="true"></i>
                      </span>
                      <input type="search" id="user-search" class="form-control" placeholder="Search users"
                        aria-label="Search users" style="width: 180px" />
                    </div>
                    <!--combo box de ROLES 👇-->
                    <select id="user-role-filter" class="form-select form-select-sm w-auto" aria-label="Filter by role">
                      <option value="all" selected>All roles</option>
                      <option value="administrator">Administrator</option>
                      <option value="editor">Editor</option>
                      <option value="author">Author</option>
                      <option value="subscriber">Subscriber</option>
                    </select>
                    <!--Boton nuevo usuario 👇-->
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                      data-bs-target="#modal-add-user">
                      <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                      New user
                    </button>
                  </div>
                </div>
              </div>
            </div>


            <!--modificar en un futuro la barra de muestra para funcionar con SQL👇-->
            <!--barra de muestra👇-->
            <div class="card-footer clearfix">
              <div class="float-start pt-1 fs-7 text-body-secondary">
                Showing 1 to 9 of 42 users
              </div>
              <!--divicion de registros << 1,2,3,4,5 >>👇📋 -->
              <ul class="pagination pagination-sm m-0 float-end">
                <li class="page-item disabled">
                  <a class="page-link" href="#" aria-label="Previous"> &laquo; </a>
                </li>
                <li class="page-item active">
                  <a class="page-link" href="#">1</a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#">3</a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#">4</a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#">5</a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#" aria-label="Next"> &raquo; </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>


      <!--ventana emergente de [👤New user]👇-->
      <div class="modal fade" id="modal-add-user" tabindex="-1" aria-labelledby="modal-add-user-label"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form>
              <div class="modal-header">
                <h5 class="modal-title" id="modal-add-user-label">Add new user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label for="new-user-name" class="form-label"> Full name </label>
                  <input type="text" class="form-control" id="new-user-name" placeholder="e.g. Jane Doe" required />
                </div>
                <div class="mb-3">
                  <label for="new-user-email" class="form-label"> Email address </label>
                  <input type="email" class="form-control" id="new-user-email" placeholder="name@example.com"
                    required />
                  <div class="form-text">The invitation will be sent to this address.</div>
                </div>
                <div class="mb-3">
                  <label for="new-user-role" class="form-label"> Role </label>
                  <select id="new-user-role" class="form-select">
                    <option selected>Subscriber</option>
                    <option>Author</option>
                    <option>Editor</option>
                    <option>Administrator</option>
                  </select>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="new-user-welcome" checked />
                  <label class="form-check-label" for="new-user-welcome">
                    Send a welcome email with login details
                  </label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary">Create user</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!--ventana emergente de [🗑️eliminar usuario]👇-->
      <div class="modal fade" id="modal-delete-user" tabindex="-1" aria-labelledby="modal-delete-user-label"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modal-delete-user-label">Delete user</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="mb-0">
                Are you sure you want to delete this user? All content owned by the account
                will be reassigned to the site administrator. This action cannot be undone.
              </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                Delete user
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
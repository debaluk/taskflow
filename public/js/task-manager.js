function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function toggleTreeNode(button) {
    const row = button.closest('.tree-row');

    if (!row) {
        return;
    }

    row.classList.toggle('expanded');
}


/*
|--------------------------------------------------------------------------
| TASK MODAL
|--------------------------------------------------------------------------
*/

async function openTaskModal(task = 'New Task') {

    const modal = document.getElementById('taskModal');

    if (!modal) {
        return;
    }

    hideAddSubtaskForm();

    /*
    |--------------------------------------------------------------------------
    | NEW TASK
    |--------------------------------------------------------------------------
    */

    if (typeof task !== 'object') {

        document.getElementById('modalTitle').textContent = task;

        document.getElementById('modalTaskTitle').value = '';
        document.getElementById('modalDescription').value = '';
        document.getElementById('modalStatus').value = 'open';
        document.getElementById('modalPriority').value = 'normal';
        document.getElementById('modalStartDate').value = '';
        document.getElementById('modalDueDate').value = '';

        delete modal.dataset.taskId;

        /*
         * Pastikan subtask lama tidak terbawa
         * saat membuka task baru.
         */
        renderSubtasks([]);

        modal.classList.remove('hidden');

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT TASK
    |--------------------------------------------------------------------------
    */

    const taskId = task.id;

    if (!taskId) {

        alert('Task ID tidak ditemukan.');

        return;
    }

    modal.dataset.taskId = taskId;

    document.getElementById('modalTitle').textContent =
        'Loading...';

    modal.classList.remove('hidden');

    try {

        const response = await fetch(
            '/tasks/' + taskId,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {

            throw new Error(
                data.message ||
                'Gagal mengambil detail task.'
            );
        }

        const detail = data.task;

        document.getElementById('modalTitle').textContent =
            'Edit Task';

        document.getElementById('modalTaskTitle').value =
            detail.title ?? '';

        document.getElementById('modalDescription').value =
            detail.description ?? '';

        document.getElementById('modalStatus').value =
            detail.status ?? 'open';

        document.getElementById('modalPriority').value =
            detail.priority ?? 'normal';

        document.getElementById('modalDueDate').value =
            detail.due_date
                ? detail.due_date.substring(0, 10)
                : '';

        document.getElementById('modalStartDate').value =
            detail.start_date
                ? detail.start_date.substring(0, 10)
                : '';

        renderSubtasks(
            detail.subtasks ?? []
        );

    } catch (error) {

        console.error(error);

        alert(
            error.message ||
            'Gagal mengambil detail task.'
        );

        closeTaskModal();
    }
}


/*
|--------------------------------------------------------------------------
| SUB TASK
|--------------------------------------------------------------------------
*/

function renderSubtasks(subtasks) {

    const list =
        document.getElementById('subtaskList');

    const counter =
        document.getElementById('subtaskCount');

    if (!list || !counter) {
        return;
    }

    counter.textContent =
        subtasks.length;


    if (subtasks.length === 0) {

        list.innerHTML = `
            <div class="subtask-empty">
                Tidak ada subtask.
            </div>
        `;

        return;
    }


    list.innerHTML = subtasks.map(subtask => {

        const safeTitle =
            escapeHtml(subtask.title ?? '');

        const safePriority =
            escapeHtml(subtask.priority ?? 'normal');

        const safeStatus =
            formatSubtaskStatus(subtask.status);

        return `
            <div
                class="subtask-item"
                onclick='openSubtaskEdit(${JSON.stringify(subtask).replace(/'/g, '&#39;')})'
            >

                <div class="subtask-info">

                    <strong>
                        ${safeTitle}
                    </strong>

                    <small>
                        ${safeStatus}
                    </small>

                </div>

                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                    "
                >

                    <span
                        class="subtask-priority ${safePriority}"
                    >
                        ${safePriority}
                    </span>

                    <button
                        type="button"
                        class="subtask-delete"
                        onclick="
                            event.stopPropagation();
                            deleteSubtask(
                                ${subtask.id},
                                ${JSON.stringify(subtask.title ?? '')}
                            )
                        "
                    >
                        Delete
                    </button>

                </div>

            </div>
        `;

    }).join('');
}


function openSubtaskEdit(subtask) {

    const title =
        document.getElementById('subtaskTitle');

    const status =
        document.getElementById('subtaskStatus');

    const priority =
        document.getElementById('subtaskPriority');

    const dueDate =
        document.getElementById('subtaskDueDate');

    const form =
        document.getElementById('addSubtaskForm');

    if (!form) {
        return;
    }

    form.classList.remove('hidden');

    title.value =
        subtask.title ?? '';

    status.value =
        subtask.status ?? 'open';

    priority.value =
        subtask.priority ?? 'normal';

    dueDate.value =
        subtask.due_date
            ? subtask.due_date.substring(0, 10)
            : '';

    form.dataset.subtaskId =
        subtask.id;

    title.focus();
}


function escapeHtml(value) {

    const div =
        document.createElement('div');

    div.textContent =
        value ?? '';

    return div.innerHTML;
}


function formatSubtaskStatus(status) {

    const labels = {

        open: 'To Do',

        in_progress: 'In Progress',

        review: 'Review',

        done: 'Done'

    };

    return labels[status] ?? status;
}


/*
|--------------------------------------------------------------------------
| CLOSE TASK MODAL
|--------------------------------------------------------------------------
*/

function closeTaskModal(e) {

    const modal =
        document.getElementById('taskModal');

    if (!modal) {
        return;
    }

    if (!e || e.target === modal) {

        modal.classList.add('hidden');

        hideAddSubtaskForm();
    }
}


/*
|--------------------------------------------------------------------------
| SAVE TASK
|--------------------------------------------------------------------------
*/

async function saveTask() {

    const modal =
        document.getElementById('taskModal');

    if (!modal) {

        alert(
            'Task modal tidak ditemukan.'
        );

        return;
    }

    const taskId =
        modal.dataset.taskId;

    const title =
        document.getElementById('modalTaskTitle')
            .value
            .trim();

    const description =
        document.getElementById('modalDescription')
            .value;

    const status =
        document.getElementById('modalStatus')
            .value;

    const priority =
        document.getElementById('modalPriority')
            .value;

    const startDate =
        document.getElementById('modalStartDate')
            .value;

    const dueDate =
        document.getElementById('modalDueDate')
            .value;

    if (!title) {

        alert(
            'Task Title wajib diisi.'
        );

        return;
    }

    const csrfToken =
        document.querySelector(
            'meta[name="csrf-token"]'
        )?.content ?? '';


    /*
    |--------------------------------------------------------------------------
    | EDIT TASK
    |--------------------------------------------------------------------------
    */

    if (taskId) {

        const form =
            document.createElement('form');

        form.method =
            'POST';

        form.action =
            '/tasks/' + taskId;

        form.innerHTML = `

            <input
                type="hidden"
                name="_token"
                value="${escapeHtml(csrfToken)}"
            >

            <input
                type="hidden"
                name="_method"
                value="PUT"
            >

            <input
                type="hidden"
                name="title"
            >

            <input
                type="hidden"
                name="description"
            >

            <input
                type="hidden"
                name="status"
            >

            <input
                type="hidden"
                name="priority"
            >

            <input
                type="hidden"
                name="start_date"
            >

            <input
                type="hidden"
                name="due_date"
            >

            <input
                type="hidden"
                name="return_url"
            >

        `;

        form.querySelector(
            '[name="title"]'
        ).value = title;

        form.querySelector(
            '[name="description"]'
        ).value = description;

        form.querySelector(
            '[name="status"]'
        ).value = status;

        form.querySelector(
            '[name="priority"]'
        ).value = priority;

        form.querySelector(
            '[name="start_date"]'
        ).value = startDate;

        form.querySelector(
            '[name="due_date"]'
        ).value = dueDate;

        form.querySelector(
            '[name="return_url"]'
        ).value = window.location.href;

        document.body.appendChild(form);

        form.submit();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | NEW TASK
    |--------------------------------------------------------------------------
    */

    const taskContext =
        window.taskContext ?? {};

    const listId =
        taskContext.list_id;

    if (!listId) {

        alert(
            'List Task belum ditentukan.'
        );

        return;
    }


    const form =
        document.createElement('form');

    form.method =
        'POST';

    form.action =
        '/tasks';

    form.innerHTML = `

        <input
            type="hidden"
            name="_token"
            value="${escapeHtml(csrfToken)}"
        >

        <input
            type="hidden"
            name="return_url"
        >

        <input
            type="hidden"
            name="list_id"
        >

        <input
            type="hidden"
            name="title"
        >

        <input
            type="hidden"
            name="description"
        >

        <input
            type="hidden"
            name="status"
        >

        <input
            type="hidden"
            name="priority"
        >

        <input
            type="hidden"
            name="start_date"
        >

        <input
            type="hidden"
            name="due_date"
        >

    `;

    form.querySelector(
        '[name="return_url"]'
    ).value =
        window.location.href;

    form.querySelector(
        '[name="list_id"]'
    ).value =
        listId;

    form.querySelector(
        '[name="title"]'
    ).value =
        title;

    form.querySelector(
        '[name="description"]'
    ).value =
        description;

    form.querySelector(
        '[name="status"]'
    ).value =
        status;

    form.querySelector(
        '[name="priority"]'
    ).value =
        priority;

    form.querySelector(
        '[name="start_date"]'
    ).value =
        startDate;

    form.querySelector(
        '[name="due_date"]'
    ).value =
        dueDate;

    document.body.appendChild(form);

    form.submit();
}


/*
|--------------------------------------------------------------------------
| DELETE TASK
|--------------------------------------------------------------------------
*/

function deleteTask() {

    const modal =
        document.getElementById('taskModal');

    if (!modal) {
        return;
    }

    const taskId =
        modal.dataset.taskId;

    if (!taskId) {

        alert(
            'Task ID tidak ditemukan.'
        );

        return;
    }

    const confirmed =
        confirm(
            'Yakin ingin menghapus task ini?'
        );

    if (!confirmed) {
        return;
    }

    const form =
        document.createElement('form');

    form.method =
        'POST';

    form.action =
        '/tasks/' + taskId;

    form.innerHTML = `

        <input
            type="hidden"
            name="_token"
            value="${escapeHtml(
                document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content ?? ''
            )}"
        >

        <input
            type="hidden"
            name="_method"
            value="DELETE"
        >

        <input
            type="hidden"
            name="return_url"
        >

    `;

    form.querySelector(
        '[name="return_url"]'
    ).value =
        window.location.href;

    document.body.appendChild(form);

    form.submit();
}


/*
|--------------------------------------------------------------------------
| KANBAN
|--------------------------------------------------------------------------
*/

let draggedTaskId = null;

window.isDraggingTask = false;


function dragTask(event) {

    draggedTaskId =
        event.currentTarget.dataset.taskId;

    window.isDraggingTask =
        true;

    event.dataTransfer.effectAllowed =
        'move';

    event.dataTransfer.setData(
        'text/plain',
        draggedTaskId
    );
}


function allowDrop(event) {

    event.preventDefault();

    const column =
        event.currentTarget;

    column.classList.add(
        'drag-over'
    );

    const card =
        document.querySelector(
            '.kanban-card[data-task-id="' +
            draggedTaskId +
            '"]'
        );

    if (!card) {
        return;
    }

    let placeholder =
        column.querySelector(
            '.kanban-placeholder'
        );

    if (!placeholder) {

        placeholder =
            document.createElement('div');

        placeholder.className =
            'kanban-placeholder';
    }

    const cards = [
        ...column.querySelectorAll(
            '.kanban-card'
        )
    ].filter(
        item => item !== card
    );

    let inserted =
        false;

    for (const targetCard of cards) {

        const rect =
            targetCard.getBoundingClientRect();

        const middle =
            rect.top +
            (rect.height / 2);

        if (event.clientY < middle) {

            column.insertBefore(
                placeholder,
                targetCard
            );

            inserted =
                true;

            break;
        }
    }

    if (!inserted) {

        const addButton =
            column.querySelector(
                '.add-card'
            );

        if (addButton) {

            column.insertBefore(
                placeholder,
                addButton
            );

        } else {

            column.appendChild(
                placeholder
            );
        }
    }
}


async function dropTask(event) {

    event.preventDefault();

    const column =
        event.currentTarget;

    const taskId =
        event.dataTransfer.getData(
            'text/plain'
        );

    column.classList.remove(
        'drag-over'
    );

    const card =
        document.querySelector(
            '.kanban-card[data-task-id="' +
            taskId +
            '"]'
        );

    const placeholder =
        column.querySelector(
            '.kanban-placeholder'
        );

    if (!card) {

        window.isDraggingTask =
            false;

        return;
    }

    const currentColumn =
        card.closest('.kanban-col');

    const emptyMessage =
        column.querySelector(
            '.kanban-empty'
        );

    if (emptyMessage) {
        emptyMessage.remove();
    }

    if (placeholder) {

        placeholder.replaceWith(
            card
        );

    } else {

        const addButton =
            column.querySelector(
                '.add-card'
            );

        if (addButton) {

            column.insertBefore(
                card,
                addButton
            );

        } else {

            column.appendChild(
                card
            );
        }
    }

    updateColumnCount(
        currentColumn
    );

    updateColumnCount(
        column
    );

    await saveKanbanOrder();
}


async function saveKanbanOrder() {

    const columns = [];

    document
        .querySelectorAll('.kanban-col')
        .forEach(column => {

            const status =
                column.dataset.status;

            const tasks = [
                ...column.querySelectorAll(
                    '.kanban-card'
                )
            ].map(card => {

                return parseInt(
                    card.dataset.taskId,
                    10
                );

            });

            columns.push({

                status: status,

                tasks: tasks

            });

        });


    try {

        const response =
            await fetch(
                '/tasks/reorder',
                {
                    method: 'PATCH',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content ?? ''

                    },

                    body:
                        JSON.stringify({
                            columns: columns
                        })
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal menyimpan urutan task.'
            );
        }

        if (data.tasks) {

            data.tasks.forEach(task => {

                const card =
                    document.querySelector(
                        '.kanban-card[data-task-id="' +
                        task.id +
                        '"]'
                    );

                if (!card) {
                    return;
                }

                const progressText =
                    card.querySelector(
                        '.task-card-progress strong'
                    );

                const progressBar =
                    card.querySelector(
                        '.task-card-progress .progress-bar span'
                    );

                if (progressText) {

                    progressText.textContent =
                        (task.progress ?? 0) +
                        '%';
                }

                if (progressBar) {

                    progressBar.style.width =
                        (task.progress ?? 0) +
                        '%';
                }

            });
        }

    } catch (error) {

        console.error(error);

        alert(
            error.message ||
            'Gagal menyimpan urutan task.'
        );

        window.location.reload();

        return;
    }

    window.isDraggingTask =
        false;
}


async function updateTaskStatus(
    taskId,
    status
) {

    const card =
        document.querySelector(
            '.kanban-card[data-task-id="' +
            taskId +
            '"]'
        );

    if (card) {

        card.classList.add(
            'updating'
        );
    }

    try {

        const response =
            await fetch(
                '/tasks/' +
                taskId +
                '/status',
                {
                    method: 'PATCH',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content ?? ''

                    },

                    body:
                        JSON.stringify({
                            status: status
                        })
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal memperbarui status.'
            );
        }

        if (card) {

            card.classList.remove(
                'updating'
            );
        }

        window.isDraggingTask =
            false;

    } catch (error) {

        window.isDraggingTask =
            false;

        alert(
            error.message ||
            'Gagal memperbarui status.'
        );

        window.location.reload();
    }
}


function updateColumnCount(column) {

    if (!column) {
        return;
    }

    const count =
        column.querySelectorAll(
            '.kanban-card'
        ).length;

    const counter =
        column.querySelector(
            '.kanban-head span'
        );

    if (counter) {

        counter.textContent =
            count;
    }
}


/*
|--------------------------------------------------------------------------
| SUBTASK FORM
|--------------------------------------------------------------------------
*/

function showAddSubtaskForm() {

    const modal =
        document.getElementById(
            'taskModal'
        );

    const form =
        document.getElementById(
            'addSubtaskForm'
        );

    if (!modal || !form) {
        return;
    }

    if (!modal.dataset.taskId) {

        alert(
            'Task belum dipilih.'
        );

        return;
    }

    /*
     * Jika membuka form untuk tambah baru,
     * jangan hapus subtaskId saat sedang edit.
     */
    if (!form.dataset.subtaskId) {

        document.getElementById(
            'subtaskTitle'
        ).value = '';

        document.getElementById(
            'subtaskStatus'
        ).value = 'open';

        document.getElementById(
            'subtaskPriority'
        ).value = 'normal';

        document.getElementById(
            'subtaskDueDate'
        ).value = '';
    }

    form.classList.remove(
        'hidden'
    );

    document.getElementById(
        'subtaskTitle'
    ).focus();
}


function hideAddSubtaskForm() {

    const form =
        document.getElementById(
            'addSubtaskForm'
        );

    if (!form) {
        return;
    }

    form.classList.add(
        'hidden'
    );

    document.getElementById(
        'subtaskTitle'
    ).value = '';

    document.getElementById(
        'subtaskStatus'
    ).value =
        'open';

    document.getElementById(
        'subtaskPriority'
    ).value =
        'normal';

    document.getElementById(
        'subtaskDueDate'
    ).value =
        '';

    delete form.dataset.subtaskId;
}


async function saveSubtask() {

    const modal =
        document.getElementById(
            'taskModal'
        );

    const form =
        document.getElementById(
            'addSubtaskForm'
        );

    if (!modal || !form) {
        return;
    }

    const taskId =
        modal.dataset.taskId;

    const subtaskId =
        form.dataset.subtaskId;

    if (!taskId) {

        alert(
            'Task ID tidak ditemukan.'
        );

        return;
    }

    const title =
        document.getElementById(
            'subtaskTitle'
        ).value.trim();

    const status =
        document.getElementById(
            'subtaskStatus'
        ).value;

    const priority =
        document.getElementById(
            'subtaskPriority'
        ).value;

    const dueDate =
        document.getElementById(
            'subtaskDueDate'
        ).value;

    if (!title) {

        alert(
            'Subtask Title wajib diisi.'
        );

        return;
    }

    try {

        let url;
        let method;

        if (subtaskId) {

            url =
                '/subtasks/' +
                subtaskId;

            method =
                'PUT';

        } else {

            url =
                '/tasks/' +
                taskId +
                '/subtasks';

            method =
                'POST';
        }

        const response =
            await fetch(
                url,
                {
                    method: method,

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content ?? ''

                    },

                    body:
                        JSON.stringify({

                            title: title,

                            status: status,

                            priority: priority,

                            due_date:
                                dueDate ||
                                null

                        })
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal menyimpan subtask.'
            );
        }

        hideAddSubtaskForm();

        await refreshTaskDetail(
            taskId
        );

    } catch (error) {

        console.error(error);

        alert(
            error.message ||
            'Gagal menyimpan subtask.'
        );
    }
}


async function deleteSubtask(
    subtaskId,
    title
) {

    const modal =
        document.getElementById(
            'taskModal'
        );

    if (!modal) {
        return;
    }

    const taskId =
        modal.dataset.taskId;

    if (!taskId) {

        alert(
            'Task ID tidak ditemukan.'
        );

        return;
    }

    if (
        !confirm(
            'Hapus subtask "' +
            title +
            '"?'
        )
    ) {

        return;
    }

    try {

        const response =
            await fetch(
                '/subtasks/' +
                subtaskId,
                {
                    method: 'DELETE',

                    headers: {

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content ?? ''

                    }
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal menghapus subtask.'
            );
        }

        await refreshTaskDetail(
            taskId
        );

    } catch (error) {

        console.error(error);

        alert(
            error.message ||
            'Gagal menghapus subtask.'
        );
    }
}


async function refreshTaskDetail(
    taskId
) {

    const response =
        await fetch(
            '/tasks/' +
            taskId,
            {
                headers: {
                    'Accept':
                        'application/json'
                }
            }
        );

    const data =
        await response.json();

    if (
        !response.ok ||
        !data.success
    ) {

        throw new Error(
            data.message ||
            'Gagal mengambil detail task.'
        );
    }

    const detail =
        data.task;

    renderSubtasks(
        detail.subtasks ?? []
    );
}


/*
|--------------------------------------------------------------------------
| DRAG END
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'dragend',
    function () {

        window.isDraggingTask =
            false;

        document
            .querySelectorAll(
                '.kanban-col'
            )
            .forEach(column => {

                column.classList.remove(
                    'drag-over'
                );

            });

        document
            .querySelectorAll(
                '.kanban-placeholder'
            )
            .forEach(item => {

                item.remove();

            });
    }
);

/*
|--------------------------------------------------------------------------
| WORKSPACE
|--------------------------------------------------------------------------
*/

function openWorkspaceModal() {

    const modal =
        document.getElementById(
            'workspaceModal'
        );

    if (!modal) {
        return;
    }

    modal.style.display =
        'block';
}


function closeWorkspaceModal(event) {

    const modal =
        document.getElementById(
            'workspaceModal'
        );

    if (!modal) {
        return;
    }

    if (
        event &&
        event.target !==
        event.currentTarget
    ) {

        return;
    }

    modal.style.display =
        'none';
}


function openWorkspaceMemberModal() {

    const modal =
        document.getElementById(
            'workspaceMemberModal'
        );

    if (!modal) {

        console.log(
            'workspaceMemberModal tidak ditemukan'
        );

        return;
    }

    modal.classList.add(
        'show'
    );
}


function closeWorkspaceMemberModal() {

    const modal =
        document.getElementById(
            'workspaceMemberModal'
        );

    if (!modal) {
        return;
    }

    modal.classList.remove(
        'show'
    );
}


/*
|--------------------------------------------------------------------------
| ASSIGNEE POPUP
|--------------------------------------------------------------------------
*/

let currentAssigneeTaskId = null;


function openAssigneePopup(
    event,
    taskId
) {

    event.stopPropagation();

    const popup =
        document.getElementById(
            'assigneePopup'
        );

    if (!popup) {
        return;
    }

    currentAssigneeTaskId =
        taskId;

    popup.style.display =
        'block';

    const search =
        document.getElementById(
            'assigneeSearch'
        );

    if (search) {

        search.value = '';

        setTimeout(
            () => search.focus(),
            50
        );
    }

    renderAssigneeMembers(
        window.systemUsers || []
    );
}


function closeAssigneePopup() {

    const popup =
        document.getElementById(
            'assigneePopup'
        );

    if (popup) {

        popup.style.display =
            'none';
    }

    currentAssigneeTaskId =
        null;
}


function renderAssigneeMembers(
    users
) {

    const list =
        document.getElementById(
            'assigneeList'
        );

    if (!list) {
        return;
    }

    if (!users.length) {

        list.innerHTML = `
            <div class="assignee-empty">
                Tidak ada user tersedia.
            </div>
        `;

        return;
    }

    list.innerHTML =
        users.map(user => {

            const initial =
                user.name
                    ? user.name
                        .charAt(0)
                        .toUpperCase()
                    : 'U';

            return `
                <button
                    type="button"
                    class="assignee-member"
                    onclick="assignTask(${user.id})"
                >

                    <span class="avatar sm">
                        ${escapeHtml(initial)}
                    </span>

                    <span>

                        <strong>
                            ${escapeHtml(user.name)}
                        </strong>

                        <small>
                            ${escapeHtml(user.email)}
                        </small>

                    </span>

                </button>
            `;

        }).join('');
}


function filterAssignees() {

    const searchInput =
        document.getElementById(
            'assigneeSearch'
        );

    const keyword =
        (
            searchInput?.value ||
            ''
        )
        .toLowerCase()
        .trim();

    const users =
        window.systemUsers || [];

    const filtered =
        users.filter(user => {

            const name =
                (
                    user.name ||
                    ''
                ).toLowerCase();

            const email =
                (
                    user.email ||
                    ''
                ).toLowerCase();

            return (
                name.includes(keyword) ||
                email.includes(keyword)
            );
        });

    renderAssigneeMembers(
        filtered
    );
}


async function assignTask(
    userId
) {

    if (!currentAssigneeTaskId) {
        return;
    }

    const taskId =
        currentAssigneeTaskId;

    try {

        const response =
            await fetch(
                `/tasks/${taskId}/assignee`,
                {
                    method: 'PATCH',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute(
                                    'content'
                                ) ?? ''

                    },

                    body:
                        JSON.stringify({
                            user_id: userId
                        })
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal mengubah PIC.'
            );
        }

        closeAssigneePopup();

        window.location.reload();

    } catch (error) {

        console.error(
            'ASSIGN TASK ERROR:',
            error
        );

        alert(
            error.message ||
            'Gagal mengubah PIC.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| CLOSE ASSIGNEE POPUP WHEN CLICK OUTSIDE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function(event) {

        const popup =
            document.getElementById(
                'assigneePopup'
            );

        if (!popup) {
            return;
        }

        if (
            popup.style.display ===
            'none'
        ) {
            return;
        }

        if (
            !popup.contains(
                event.target
            )
        ) {

            closeAssigneePopup();
        }
    }
);


/*
|--------------------------------------------------------------------------
| GANTT DEPENDENCY DRAG
|--------------------------------------------------------------------------
*/

let dependencyDragging = false;

let dependencySourceTaskId = null;

let dependencyLine = null;

let dependencyPendingSourceTaskId = null;

let dependencyPendingTargetTaskId = null;


document.addEventListener(
    'DOMContentLoaded',
    function() {

        const handles =
            document.querySelectorAll(
                '.dependency-source'
            );

        handles.forEach(
            function(handle) {

                handle.addEventListener(
                    'mousedown',
                    startDependencyDrag
                );

            }
        );

        renderGanttDependencies();

        window.addEventListener(
            'resize',
            renderGanttDependencies
        );
    }
);


/*
|--------------------------------------------------------------------------
| START DEPENDENCY DRAG
|--------------------------------------------------------------------------
*/

function startDependencyDrag(
    event
) {

    event.preventDefault();

    event.stopPropagation();

    dependencyDragging =
        true;

    dependencySourceTaskId =
        event.currentTarget.dataset.taskId;

    createDependencyDragLine();

    document.addEventListener(
        'mousemove',
        moveDependencyDragLine
    );

    document.addEventListener(
        'mouseup',
        finishDependencyDrag
    );
}


/*
|--------------------------------------------------------------------------
| TEMPORARY DRAG LINE
|--------------------------------------------------------------------------
*/

function createDependencyDragLine() {

    dependencyLine =
        document.createElement('div');

    dependencyLine.className =
        'dependency-drag-line';

    document.body.appendChild(
        dependencyLine
    );
}


function moveDependencyDragLine(
    event
) {

    if (!dependencyDragging) {
        return;
    }

    const sourceHandle =
        document.querySelector(
            '.dependency-source[data-task-id="' +
            dependencySourceTaskId +
            '"]'
        );

    if (!sourceHandle) {
        return;
    }

    const sourceRect =
        sourceHandle.getBoundingClientRect();

    const x1 =
        sourceRect.left +
        (sourceRect.width / 2);

    const y1 =
        sourceRect.top +
        (sourceRect.height / 2);

    const x2 =
        event.clientX;

    const y2 =
        event.clientY;

    const dx =
        x2 - x1;

    const dy =
        y2 - y1;

    const length =
        Math.sqrt(
            (dx * dx) +
            (dy * dy)
        );

    const angle =
        Math.atan2(dy, dx) *
        (180 / Math.PI);

    if (dependencyLine) {

        dependencyLine.style.width =
            length + 'px';

        dependencyLine.style.left =
            x1 + 'px';

        dependencyLine.style.top =
            y1 + 'px';

        dependencyLine.style.transform =
            'rotate(' +
            angle +
            'deg)';
    }
}


/*
|--------------------------------------------------------------------------
| FINISH DEPENDENCY DRAG
|--------------------------------------------------------------------------
*/

async function finishDependencyDrag(
    event
) {

    if (!dependencyDragging) {
        return;
    }

    const target =
        event.target.closest(
            '.dependency-target'
        );

    const sourceTaskId =
        dependencySourceTaskId;

    cleanupDependencyDrag();

    if (!target) {
        return;
    }

    const targetTaskId =
        target.dataset.taskId;

    if (
        !sourceTaskId ||
        !targetTaskId
    ) {
        return;
    }

    if (
        String(sourceTaskId) ===
        String(targetTaskId)
    ) {

        alert(
            'Task tidak boleh tergantung pada dirinya sendiri.'
        );

        return;
    }

    dependencyPendingSourceTaskId =
        sourceTaskId;

    dependencyPendingTargetTaskId =
        targetTaskId;


    /*
    |--------------------------------------------------------------------------
    | CARI DEPENDENCY EXISTING
    |--------------------------------------------------------------------------
    */

    let currentDependencyType =
        'FS';

    const existingDependencies =
        window.ganttDependencies?.[
            targetTaskId
        ] || [];

    const existingDependency =
        existingDependencies.find(
            function(dependency) {

                return String(
                    dependency.depends_on_task_id
                ) === String(
                    sourceTaskId
                );
            }
        );

    if (existingDependency) {

        currentDependencyType =
            existingDependency.dependency_type ||
            'FS';
    }


    /*
    |--------------------------------------------------------------------------
    | RADIO TYPE
    |--------------------------------------------------------------------------
    */

    const selectedType =
        document.querySelector(
            'input[name="dependency_type"][value="' +
            currentDependencyType +
            '"]'
        );

    if (selectedType) {

        selectedType.checked =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | POPUP
    |--------------------------------------------------------------------------
    */

    const popup =
        document.getElementById(
            'dependencyTypePopup'
        );

    if (popup) {

        popup.style.display =
            'block';
    }
}


/*
|--------------------------------------------------------------------------
| SAVE DEPENDENCY + TYPE
|--------------------------------------------------------------------------
*/

async function saveDependencyWithType() {

    const sourceTaskId =
        dependencyPendingSourceTaskId;

    const targetTaskId =
        dependencyPendingTargetTaskId;

    if (
        !sourceTaskId ||
        !targetTaskId
    ) {
        return;
    }

    const selectedType =
        document.querySelector(
            'input[name="dependency_type"]:checked'
        );

    if (!selectedType) {

        alert(
            'Pilih jenis dependency terlebih dahulu.'
        );

        return;
    }

    const dependencyType =
        selectedType.value;

    try {

        const response =
            await fetch(
                '/tasks/' +
                targetTaskId +
                '/dependency',
                {
                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute(
                                    'content'
                                ) ?? ''
                    },

                    body:
                        JSON.stringify({

                            depends_on_task_id:
                                sourceTaskId,

                            dependency_type:
                                dependencyType

                        })
                }
            );

        const data =
            await response.json();

        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                'Gagal menyimpan dependency.'
            );
        }

        if (
            !window.ganttDependencies
        ) {

            window.ganttDependencies =
                {};
        }

        const dependencyList =
            window.ganttDependencies[
                targetTaskId
            ] || [];

        const existingIndex =
            dependencyList.findIndex(
                function(dependency) {

                    return String(
                        dependency.depends_on_task_id
                    ) === String(
                        sourceTaskId
                    );
                }
            );

        const updatedDependency = {

            id:
                data.dependency.id,

            task_id:
                targetTaskId,

            depends_on_task_id:
                sourceTaskId,

            dependency_type:
                data.dependency.dependency_type

        };

        if (existingIndex >= 0) {

            dependencyList[
                existingIndex
            ] =
                updatedDependency;

        } else {

            dependencyList.push(
                updatedDependency
            );
        }

        window.ganttDependencies[
            targetTaskId
        ] =
            dependencyList;

        closeDependencyTypePopup();

        renderGanttDependencies();

        console.log(
            'Dependency berhasil disimpan:',
            targetTaskId,
            'depends on',
            sourceTaskId,
            'type:',
            dependencyType
        );

    } catch (error) {

        console.error(
            'DEPENDENCY ERROR:',
            error
        );

        alert(
            error.message ||
            'Gagal menyimpan dependency.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| CLOSE DEPENDENCY TYPE POPUP
|--------------------------------------------------------------------------
*/

function closeDependencyTypePopup() {

    const popup =
        document.getElementById(
            'dependencyTypePopup'
        );

    if (popup) {

        popup.style.display =
            'none';
    }

    dependencyPendingSourceTaskId =
        null;

    dependencyPendingTargetTaskId =
        null;
}


/*
|--------------------------------------------------------------------------
| CLEANUP DEPENDENCY DRAG
|--------------------------------------------------------------------------
*/

function cleanupDependencyDrag() {

    dependencyDragging =
        false;

    dependencySourceTaskId =
        null;

    if (dependencyLine) {

        dependencyLine.remove();

        dependencyLine =
            null;
    }

    document.removeEventListener(
        'mousemove',
        moveDependencyDragLine
    );

    document.removeEventListener(
        'mouseup',
        finishDependencyDrag
    );
}


/*
|--------------------------------------------------------------------------
| GANTT DEPENDENCY LINES
|--------------------------------------------------------------------------
*/

function renderGanttDependencies() {

    const svg =
        document.getElementById(
            'ganttDependencies'
        );

    if (!svg) {
        return;
    }

    svg.innerHTML = '';

    document
        .querySelectorAll('.gantt-line')
        .forEach(
            function(line) {

                const taskId =
                    line.dataset.taskId;

                if (!taskId) {
                    return;
                }

                const dependencies =
                    window.ganttDependencies?.[
                        taskId
                    ] || [];

                dependencies.forEach(
                    function(dependency) {

                        drawGanttDependency(
                            svg,

                            dependency.depends_on_task_id,

                            taskId,

                            dependency.dependency_type
                        );

                    }
                );
            }
        );
}


/*
|--------------------------------------------------------------------------
| DRAW DEPENDENCY LINE
|--------------------------------------------------------------------------
*/

function drawGanttDependency(
    svg,
    sourceTaskId,
    targetTaskId,
    dependencyType
) {

    const source =
        document.querySelector(
            '.dependency-source[data-task-id="' +
            sourceTaskId +
            '"]'
        );

    const target =
        document.querySelector(
            '.dependency-target[data-task-id="' +
            targetTaskId +
            '"]'
        );

    if (!source || !target) {
        return;
    }

    const svgRect =
        svg.getBoundingClientRect();

    const sourceRect =
        source.getBoundingClientRect();

    const targetRect =
        target.getBoundingClientRect();


    /*
    |--------------------------------------------------------------------------
    | COORDINATE
    |--------------------------------------------------------------------------
    */

    const x1 =
        sourceRect.left +
        sourceRect.width / 2 -
        svgRect.left;

    const y1 =
        sourceRect.top +
        sourceRect.height / 2 -
        svgRect.top;

    const x2 =
        targetRect.left +
        targetRect.width / 2 -
        svgRect.left;

    const y2 =
        targetRect.top +
        targetRect.height / 2 -
        svgRect.top;


    /*
    |--------------------------------------------------------------------------
    | DEPENDENCY COLORS
    |--------------------------------------------------------------------------
    |
    | FS = Blue
    | SS = Green
    | FF = Orange
    | SF = Purple
    |
    */

    const dependencyColors = {

        FS: '#2563eb',

        SS: '#16a34a',

        FF: '#f97316',

        SF: '#9333ea'

    };

    const color =
        dependencyColors[
            dependencyType
        ] ||
        dependencyColors.FS;


    /*
    |--------------------------------------------------------------------------
    | ORTHOGONAL LINE
    |--------------------------------------------------------------------------
    */

    const middleX =
        x1 +
        Math.max(
            20,
            (x2 - x1) / 2
        );


    const path =
        document.createElementNS(
            'http://www.w3.org/2000/svg',
            'path'
        );


    const pathData =
        'M ' +
        x1 +
        ' ' +
        y1 +

        ' L ' +
        middleX +
        ' ' +
        y1 +

        ' L ' +
        middleX +
        ' ' +
        y2 +

        ' L ' +
        x2 +
        ' ' +
        y2;


    path.setAttribute(
        'd',
        pathData
    );

    path.setAttribute(
        'stroke',
        color
    );

    path.setAttribute(
        'stroke-width',
        '2'
    );

    path.setAttribute(
        'fill',
        'none'
    );

    svg.appendChild(
        path
    );


    /*
    |--------------------------------------------------------------------------
    | ARROW HEAD
    |--------------------------------------------------------------------------
    */

    const arrow =
        document.createElementNS(
            'http://www.w3.org/2000/svg',
            'polygon'
        );

    const arrowSize =
        5;


    /*
     * Arrow dibuat mengikuti arah
     * horizontal menuju target.
     */

    const arrowPoints = [
        x2 + ',' + y2,

        (x2 - arrowSize) +
            ',' +
            (y2 - arrowSize),

        (x2 - arrowSize) +
            ',' +
            (y2 + arrowSize)

    ].join(' ');


    arrow.setAttribute(
        'points',
        arrowPoints
    );

    arrow.setAttribute(
        'fill',
        color
    );

    svg.appendChild(
        arrow
    );
}


/*
|--------------------------------------------------------------------------
| GLOBAL ESCAPE
|--------------------------------------------------------------------------
|
| Tutup popup/modal dengan tombol ESC.
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event) {

        if (
            event.key !==
            'Escape'
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ASSIGNEE
        |--------------------------------------------------------------------------
        */

        const assigneePopup =
            document.getElementById(
                'assigneePopup'
            );

        if (
            assigneePopup &&
            assigneePopup.style.display !==
            'none'
        ) {

            closeAssigneePopup();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DEPENDENCY TYPE
        |--------------------------------------------------------------------------
        */

        const dependencyPopup =
            document.getElementById(
                'dependencyTypePopup'
            );

        if (
            dependencyPopup &&
            dependencyPopup.style.display !==
            'none'
        ) {

            closeDependencyTypePopup();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | TASK MODAL
        |--------------------------------------------------------------------------
        */

        const taskModal =
            document.getElementById(
                'taskModal'
            );

        if (
            taskModal &&
            !taskModal.classList.contains(
                'hidden'
            )
        ) {

            closeTaskModal();
        }

    }
);


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE DEPENDENCY POPUP
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function(event) {

        const popup =
            document.getElementById(
                'dependencyTypePopup'
            );

        if (!popup) {
            return;
        }

        if (
            popup.style.display ===
            'none'
        ) {
            return;
        }

        if (
            !popup.contains(
                event.target
            )
        ) {

            closeDependencyTypePopup();
        }
    }
);


/*
|--------------------------------------------------------------------------
| WINDOW LOAD
|--------------------------------------------------------------------------
|
| Render ulang dependency setelah seluruh
| elemen Gantt selesai dirender.
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'load',
    function() {

        setTimeout(
            function() {

                renderGanttDependencies();

            },
            50
        );
    }
);

/*
|--------------------------------------------------------------------------
| SPACE ACTION POPUP
|--------------------------------------------------------------------------
*/

let currentSpaceActionId = null;
let currentSpaceActionSlug = null;


/*
|--------------------------------------------------------------------------
| OPEN SPACE ACTION MENU
|--------------------------------------------------------------------------
*/

function openSpaceActionMenu(event,
    spaceId,
    spaceName = null,
    spaceSlug = null)
{
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const popup =
        document.getElementById('spaceActionPopup');

    if (!popup) {
        console.error(
            'spaceActionPopup tidak ditemukan.'
        );

        return;
    }

    currentSpaceActionId =
        spaceId;
	currentSpaceActionSlug =
    spaceSlug;


    /*
     * Ambil nama Space.
     *
     * Prioritas:
     * 1. Nama yang dikirim dari Blade.
     * 2. Nama dari row Space.
     */
    if (!spaceName) {

        const row =
            event?.currentTarget?.closest(
                '.tree-space-row'
            );

        spaceName =
            row?.querySelector(
                '.tree-space-link .tree-name'
            )
            ?.textContent
            ?.trim() || 'Space';
    }


    const title =
        document.getElementById(
            'spaceActionTitle'
        );

    if (title) {

        title.textContent =
            spaceName;
    }


    /*
     * Tampilkan popup.
     */
    popup.style.display =
        'block';


    /*
     * Posisi popup dekat tombol +.
     */
    const button =
        event?.currentTarget;

    if (button) {

        const rect =
            button.getBoundingClientRect();

        popup.style.position =
            'fixed';

        popup.style.left =
            Math.min(
                rect.right + 8,
                window.innerWidth -
                popup.offsetWidth -
                16
            ) + 'px';

        popup.style.top =
            Math.min(
                rect.top,
                window.innerHeight -
                popup.offsetHeight -
                16
            ) + 'px';
    }
}


/*
|--------------------------------------------------------------------------
| CLOSE SPACE ACTION MENU
|--------------------------------------------------------------------------
*/

function closeSpaceActionMenu()
{
    const popup =
        document.getElementById(
            'spaceActionPopup'
        );

    if (popup) {

        popup.style.display =
            'none';
    }

    currentSpaceActionId =
    null;

	currentSpaceActionSlug =
		null;
}


/*
|--------------------------------------------------------------------------
| CREATE LIST MODAL
|--------------------------------------------------------------------------
*/

function openCreateListModal()
{
    const modal =
        document.getElementById(
            'createListModal'
        );

    const form =
        document.getElementById(
            'createListForm'
        );

    const spaceIdInput =
        document.getElementById(
            'createListSpaceId'
        );

    const spaceNameInput =
        document.getElementById(
            'createListSpaceName'
        );

    const nameInput =
        document.getElementById(
            'createListName'
        );

    if (
        !modal ||
        !form ||
        !spaceIdInput ||
        !spaceNameInput
    ) {

        console.error(
            'Create List modal tidak lengkap.'
        );

        return;
    }


    const spaceId =
    currentSpaceActionId;

	const spaceSlug =
		currentSpaceActionSlug;

	if (!spaceId || !spaceSlug) {
		alert(
			'Space tidak ditemukan.'
		);

		return;
	}


    /*
     * Ambil nama Space dari judul
     * Space Action Popup.
     */
    const spaceName =
        document.getElementById(
            'spaceActionTitle'
        )?.textContent?.trim() ||
        'Space';


    /*
     * Simpan Space ID.
     */
    spaceIdInput.value =
        spaceId;


    /*
     * Tampilkan nama Space.
     *
     * Field ini readonly sehingga
     * user tidak bisa mengganti lokasi.
     */
    spaceNameInput.value =
        spaceName;


    /*
     * Set action form berdasarkan
     * Space yang dipilih.
     *
     * Contoh:
     * /workspaces/1/spaces/7/lists
     */
    const template =
        form.dataset.storeTemplate;

    if (!template) {

        console.error(
            'Create List store template tidak ditemukan.'
        );

        return;
    }


    form.action =
    template.replace(
        '__SPACE_SLUG__',
        encodeURIComponent(spaceSlug)
    );


    /*
     * Reset nama List setiap kali
     * modal dibuka.
     */
    if (nameInput) {

        nameInput.value = '';
    }


    /*
     * Tutup Space Action Popup
     * sebelum modal Create List tampil.
     */
    closeSpaceActionMenu();


    /*
     * Tampilkan modal.
     */
    modal.classList.remove(
        'hidden'
    );


    /*
     * Fokus ke Nama List.
     */
    setTimeout(
        function () {

            if (nameInput) {

                nameInput.focus();
            }

        },
        50
    );
}


/*
|--------------------------------------------------------------------------
| CLOSE CREATE LIST MODAL
|--------------------------------------------------------------------------
*/

function closeCreateListModal(event)
{
    const modal =
        document.getElementById(
            'createListModal'
        );

    if (!modal) {
        return;
    }


    /*
     * Kalau dipanggil dari klik backdrop,
     * hanya tutup jika yang diklik memang
     * backdrop.
     */
    if (
        event &&
        event.target !== event.currentTarget
    ) {

        return;
    }


    modal.classList.add(
        'hidden'
    );


    /*
     * Bersihkan field.
     */
    const nameInput =
        document.getElementById(
            'createListName'
        );

    const spaceIdInput =
        document.getElementById(
            'createListSpaceId'
        );

    const spaceNameInput =
        document.getElementById(
            'createListSpaceName'
        );

    if (nameInput) {

        nameInput.value =
            '';
    }

    if (spaceIdInput) {

        spaceIdInput.value =
            '';
    }

    if (spaceNameInput) {

        spaceNameInput.value =
            '';
    }
}


/*
|--------------------------------------------------------------------------
| HANDLE SPACE ACTION
|--------------------------------------------------------------------------
*/

function handleSpaceAction(action)
{
    const spaceId =
        currentSpaceActionId;


    if (!spaceId) {

        alert(
            'Space tidak ditemukan.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    |
    | Sekarang tidak lagi redirect ke Space.
    | Langsung buka Create List Modal.
    |
    */

    if (action === 'list') {

        openCreateListModal();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | FOLDER
    |--------------------------------------------------------------------------
    */

    if (action === 'folder') {

        closeSpaceActionMenu();

        alert(
            'Create Folder akan dibuat dari context Space.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT
    |--------------------------------------------------------------------------
    */

    if (action === 'import') {

        closeSpaceActionMenu();

        alert(
            'Fitur Import Space belum diaktifkan.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    if (action === 'export') {

        closeSpaceActionMenu();

        alert(
            'Fitur Export Space belum diaktifkan.'
        );

        return;
    }


    console.warn(
        'Unknown Space action:',
        action
    );
}


/*
|--------------------------------------------------------------------------
| CLOSE SPACE ACTION POPUP OUTSIDE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function (event) {

        const popup =
            document.getElementById(
                'spaceActionPopup'
            );

        if (!popup) {
            return;
        }

        if (
            popup.style.display ===
            'none'
        ) {
            return;
        }

        if (
            !popup.contains(
                event.target
            )
        ) {

            closeSpaceActionMenu();
        }
    }
);


/*
|--------------------------------------------------------------------------
| GLOBAL ESCAPE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key !==
            'Escape'
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE LIST MODAL
        |--------------------------------------------------------------------------
        */

        const createListModal =
            document.getElementById(
                'createListModal'
            );

        if (
            createListModal &&
            !createListModal.classList.contains(
                'hidden'
            )
        ) {

            closeCreateListModal();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SPACE ACTION POPUP
        |--------------------------------------------------------------------------
        */

        const spaceActionPopup =
            document.getElementById(
                'spaceActionPopup'
            );

        if (
            spaceActionPopup &&
            spaceActionPopup.style.display !==
            'none'
        ) {

            closeSpaceActionMenu();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ASSIGNEE
        |--------------------------------------------------------------------------
        */

        const assigneePopup =
            document.getElementById(
                'assigneePopup'
            );

        if (
            assigneePopup &&
            assigneePopup.style.display !==
            'none'
        ) {

            closeAssigneePopup();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DEPENDENCY TYPE
        |--------------------------------------------------------------------------
        */

        const dependencyPopup =
            document.getElementById(
                'dependencyTypePopup'
            );

        if (
            dependencyPopup &&
            dependencyPopup.style.display !==
            'none'
        ) {

            closeDependencyTypePopup();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | TASK MODAL
        |--------------------------------------------------------------------------
        */

        const taskModal =
            document.getElementById(
                'taskModal'
            );

        if (
            taskModal &&
            !taskModal.classList.contains(
                'hidden'
            )
        ) {

            closeTaskModal();

        }

    }
);


/*
|--------------------------------------------------------------------------
| CLOSE CREATE LIST MODAL OUTSIDE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'createListModal'
            );

        if (!modal) {
            return;
        }

        if (
            modal.classList.contains(
                'hidden'
            )
        ) {
            return;
        }

        /*
         * Hanya tutup jika klik benar-benar
         * pada backdrop.
         */
        if (
            event.target === modal
        ) {

            closeCreateListModal();
        }
    }
);
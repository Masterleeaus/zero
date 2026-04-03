@extends('layouts.app')

@section('pageTitle', 'Create Workflow')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

@section('content')
<style>
    .milestone {
        border: 2px solid #007bff;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 20px;
        background: #f0f8ff;
        cursor: pointer;
    }
    .task, .branch {
        border: 1px solid #17a2b8;
        border-radius: 5px;
        padding: 5px 10px;
        margin: 5px 0;
        background: #e0f7fa;
        cursor: pointer;
    }
    .branch {
        background: #fff3cd;
        border-color: #ffc107;
        font-weight: bold;
    }
    .task-container {
        margin-left: 20px;
        padding-left: 10px;
        border-left: 2px dashed #6c757d;
    }
    .task.decision{background-color: #fff9c4;}
    .decision .task-container{min-height: 20px;}
    .branch .task-container{min-height: 20px;}
</style>
<div class="content-wrapper">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create New Workflow</h5>
        </div>
        <div class="card-body">
            <form id="workflowForm" onsubmit="submitWorkflow(event)">
                <div class="row">
                    <div class="col-sm-6">
                        
                        <x-forms.text fieldId="name" :fieldLabel="__('Workflow Name')" fieldName="name" fieldRequired="true"
                        fieldPlaceholder="Enter workflow name">
                         </x-forms.text>
                    </div>
                    <div class="col-sm-6">
                        <x-forms.select fieldId="project_category_id" :fieldLabel="__('Project Category')" search="true"
                                        data-live-search="true" data-size="8"
                                        fieldName="project_category_id" fieldRequired="true">
                                        @foreach ($projectCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                    @endforeach
                        </x-forms.select>

                    </div>
                    <div class="col-sm-12">

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter workflow description (optional)"></textarea>
                </div>
                    </div>

                </div>


               

                <input type="hidden" id="company_id" value="{{ company()->id }}">
                <div class=" mt-4">
                
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" onclick="addMilestone()">Add Milestone</button>
                        <button type="button" class="btn btn-secondary" onclick="addTask('regular')">Add Regular Task</button>
                        <button type="button" class="btn btn-warning" onclick="addTask('decision')">Add Decision Task</button>
                        <button type="button" class="btn btn-success" onclick="addBranch()">Add Branch</button>
                        {{-- <button type="button" class="btn btn-info" onclick="saveWorkflow()">Save Workflow</button> --}}
                    </div>
                    <div id="milestone-container"></div>
                </div>
                <div class="text-right">
                    <a href="{{ route('workflow.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Workflow</button>
                </div>
            </form>

           
        
            <!-- Modal for Editing Milestones -->
            <div class="modal fade" id="milestoneModal" tabindex="-1" aria-labelledby="milestoneModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="milestoneModalLabel">Edit Milestone</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label for="milestone-title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="milestone-title" placeholder="Enter milestone title">
                                </div>

                                <div class="mb-3">
                                    <x-forms.number 
                                        fieldId="milestone-start" 
                                        :fieldLabel="__('Relative Start Date')" 
                                        fieldName="milestone_start" 
                                        fieldRequired="true"
                                        fieldPlaceholder="(0) Days">
                                    </x-forms.number>
                                </div>
                                
                                <div class="mb-3">
                                    <x-forms.number 
                                        fieldId="milestone-end" 
                                        :fieldLabel="__('Relative End Date')" 
                                        fieldName="milestone_end" 
                                        fieldRequired="true"
                                        fieldPlaceholder="(7) Days">
                                    </x-forms.number>
                                </div>

                                <div class="mb-3">
                                    <label for="milestone-description" class="form-label">Description</label>
                                    <textarea class="form-control" id="milestone-description" rows="4" placeholder="Enter milestone description"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeMilestone()" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="saveMilestone()">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Modal for Editing Tasks -->
            <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="taskModalLabel">Edit Task</h5>
                            <button type="button" class="btn-close" onclick="closeTask()" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <x-forms.text 
                                    fieldId="task-title" 
                                    :fieldLabel="__('Title')" 
                                    fieldName="milestone_end" 
                                    fieldRequired="true"
                                    fieldPlaceholder="title">
                                </x-forms.text>

                                </div>

                                <div class="mb-3">
                                    <x-forms.number 
                                        fieldId="task-start" 
                                        :fieldLabel="__('Relative Start Date')" 
                                        fieldName="milestone_start" 
                                        fieldRequired="true"
                                        fieldPlaceholder="(0) Days">
                                    </x-forms.number>
                                </div>
                                
                                <div class="mb-3">
                                    <x-forms.number 
                                        fieldId="task-end" 
                                        :fieldLabel="__('Relative End Date')" 
                                        fieldName="milestone_end" 
                                        fieldRequired="true"
                                        fieldPlaceholder="(7) Days">
                                    </x-forms.number>
                                </div>
                                <div class="mb-3">
                                    <label for="task-description" class="form-label">Description</label>
                                    <textarea class="form-control" id="task-description" rows="4" placeholder="Enter task description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="task-templates" class="form-label">Template IDs</label>
                                    <select id="template-dropdown" class="form-control selectpicker" data-live-search="true">
                                        <!-- Options will be dynamically populated -->
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary mt-2" onclick="addTemplate()">Add Template</button>
                                    <div id="template-list" class="mt-2">
                                        <!-- Selected templates will appear here -->
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeTask()" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="saveTask()">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="successMessage" class="alert alert-success mt-3" style="display:none;"></div>
            <div id="errorMessage" class="alert alert-danger mt-3" style="display:none;"></div>
        </div>
    </div>
</div>
<script>
    let milestoneCounter = 1;
    let taskCounter = 1;
    let branchCounter = 1;
    let currentMilestone = null;
    let currentTask = null;
function addMilestone() {
const container = document.getElementById('milestone-container');
const milestone = document.createElement('div');
milestone.classList.add('milestone');
milestone.dataset.id = `milestone-${milestoneCounter}`;
milestone.dataset.description = '';
milestone.dataset.start = 0;
milestone.dataset.end = 0;
milestone.innerHTML = `
    <h3>
       <span class="title"> Milestone ${milestoneCounter}</span>
        <button type="button" class="btn btn-sm btn-link" onclick="openMilestoneModal(this.parentElement.parentElement)">
            <i class="bi bi-pencil-square"></i>
        </button>
        <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteMilestone(this.parentElement.parentElement)">
            <i class="bi bi-trash"></i>
        </button>
    </h3>
    <div class="task-container"></div>
`;
container.appendChild(milestone);
milestoneCounter++;
initializeSortable();
}

// Delete the milestone
function deleteMilestone(milestone) {
if (confirm('Are you sure you want to delete this milestone?')) {
    milestone.remove();
}
}

    function openMilestoneModal(milestone) {
        currentMilestone = milestone;
        const title = milestone.querySelector('h3').innerText;
        const description = milestone.dataset.description || '';
        const start = milestone.dataset.start || '';
        const end = milestone.dataset.end || '';
        document.getElementById('milestone-title').value = title;
        document.getElementById('milestone-description').value = description;
        document.getElementById('milestone-start').value = start;
        document.getElementById('milestone-end').value = end;
        $('#milestoneModal').modal("show");
    }

    function closeMilestone(){
    $("#milestoneModal").modal('hide');

}
    function saveMilestone() {
        const title = document.getElementById('milestone-title').value;
        const description = document.getElementById('milestone-description').value;
        if (currentMilestone) {
            currentMilestone.querySelector('span').innerText = title;
            currentMilestone.dataset.description = description;
        }

        $('#milestoneModal').modal("hide");
    
    }
function addTask(type) {
const milestones = document.querySelectorAll('.milestone');
if (!milestones.length) {
    alert('Please add a milestone first.');
    return;
}

const lastMilestone = milestones[milestones.length - 1];
const taskContainer = lastMilestone.querySelector('.task-container');
const task = document.createElement('div');
task.classList.add('task');
if (type === 'decision') task.classList.add('decision');
task.dataset.id = `task-${taskCounter}`;
task.dataset.type = type;
task.dataset.title = '';
task.dataset.start = 0;
task.dataset.end = 0;
task.dataset.description = '';
task.dataset.templates = JSON.stringify([]);
task.innerHTML = `
    <span class="task-title">${type === 'regular' ? 'Task' : 'Decision Task'} ${taskCounter}</span>
    <button type="button" class="btn btn-sm btn-link" onclick="openTaskModal(this.parentElement)">
        <i class="bi bi-pencil-square"></i>
    </button>
    <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteTask(this.parentElement)">
        <i class="bi bi-trash"></i>
    </button>
    ${type === 'decision' ? `<div class="task-container"></div>` : ''}
`;
taskContainer.appendChild(task);
taskCounter++;
initializeSortable();
}

// Delete the task
function deleteTask(task) {
if (confirm('Are you sure you want to delete this task?')) {
    task.remove();
}
}


function openTaskModal(task) {
    currentTask = task;

    // Parse the task attributes
    const title = task.dataset.title || '';
    const description = task.dataset.description || '';
    let templates;
    try {
        templates = JSON.parse(task.dataset.templates || '[]');
    } catch (error) {
        console.error('Invalid JSON in data-templates:', error);
        templates = []; // Default to an empty array if parsing fails
    }

    // Populate the modal fields
    document.getElementById('task-title').value = title;
    document.getElementById('task-description').value = description;

    document.getElementById('task-start').value = start;
    document.getElementById('task-end').value = end;

    // Populate the template list
    const templateList = document.getElementById('template-list');
    templateList.innerHTML = templates
        .map(
            (template, index) => `
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span data-id="${template.id}" data-title="${template.title}">${template.title}</span>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeTemplate(${index})">Remove</button>
        </div>
    `
        )
        .join('');

    // Show the modal
    $("#taskModal").modal("show");
}


// Update the saveTask function to include both title and id in JSON
function saveTask() {
    const title = document.getElementById('task-title').value;
    const description = document.getElementById('task-description').value;

    const start = document.getElementById('task-start').value;
    const end = document.getElementById('task-end').value;

    // Collect templates from the list
    const templates = Array.from(document.getElementById('template-list').children).map((child) => ({
        id: child.querySelector('span').dataset.id,
        title: child.querySelector('span').dataset.title,
    }));

    if (currentTask) {
        // Update the task's dataset attributes
        currentTask.dataset.title = title;
        currentTask.dataset.description = description;

        currentTask.dataset.start = start;
        currentTask.dataset.end = end;
        currentTask.dataset.templates = JSON.stringify(templates);

        // Update the visible title in the task's <span>
        const titleElement = currentTask.querySelector('span');
        if (titleElement) {
            titleElement.innerText = title;
        }
    }

    // Close the modal
    $("#taskModal").modal("hide");
}


function closeTask(){
    $("#taskModal").modal('hide');

}

    function addBranch() {
        const decisionTasks = document.querySelectorAll('.decision');
        if (!decisionTasks.length) {
            alert('Please add a decision task first.');
            return;
        }

        const lastDecisionTask = decisionTasks[decisionTasks.length - 1];
        const taskContainer = lastDecisionTask.querySelector('.task-container');
        const branch = document.createElement('div');
        branch.classList.add('branch');
        branch.dataset.id = `branch-${branchCounter}`;
        
        branch.innerHTML = `<span contenteditable="true" >Branch ${branchCounter}</span>
            <button type="button" class="btn btn-sm btn-link" onclick="openTaskModal(this.parentElement)">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteTask(this.parentElement)">
                <i class="bi bi-trash"></i>
            </button>

        <div class="task-container"></div>`;

        taskContainer.appendChild(branch);
        branchCounter++;
        initializeSortable();
    }

// Example templates data passed from the backend
const templates = {!! json_encode($letterTemplates) !!}; // Assuming $letterTemplates is passed to the view

// Populate the dropdown
function populateTemplateDropdown() {
    const dropdown = document.getElementById('template-dropdown');
    dropdown.innerHTML = ''; // Clear existing options

    templates.forEach((template) => {
        const option = document.createElement('option');
        option.value = template.id;
        option.textContent = template.title;
        dropdown.appendChild(option);
    });

    // Reinitialize the Bootstrap-Select dropdown
    $('.selectpicker').selectpicker('refresh');
}

// Add the selected template to the list
function addTemplate() {
    const dropdown = document.getElementById('template-dropdown');
    const selectedOption = dropdown.options[dropdown.selectedIndex];
    if (!selectedOption) return;

    const templateId = selectedOption.value;
    const templateName = selectedOption.text;

    // Check for duplicates
    const existingTemplates = Array.from(document.getElementById('template-list').children).map(
        (child) => child.querySelector('span').dataset.id
    );
    if (existingTemplates.includes(templateId)) {
        alert('Template already added!');
        return;
    }

    // Add to the template list
    const templateList = document.getElementById('template-list');
    const newTemplate = document.createElement('div');
    newTemplate.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'mb-1');
    newTemplate.innerHTML = `
        <span data-id="${templateId}" data-title="${templateName}">${templateName}</span>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeTemplate(this)">Remove</button>
    `;
    templateList.appendChild(newTemplate);

    // Clear the dropdown selection
    dropdown.value = '';
    $('.selectpicker').selectpicker('refresh');
}


// Remove a template from the list
function removeTemplate(button) {
    button.parentElement.remove();
}


// Populate the dropdown on page load
document.addEventListener('DOMContentLoaded', () => {
    populateTemplateDropdown();
});


    function initializeSortable() {
        new Sortable(document.getElementById('milestone-container'), {
            group: 'milestones',
            animation: 150,
        });

        document.querySelectorAll('.task-container').forEach(taskContainer => {
            new Sortable(taskContainer, {
                group: 'tasks',
                animation: 150,
            });
        });
    }

    async function submitWorkflow(event) {
        event.preventDefault();

        // Clear previous messages
        document.getElementById('successMessage').style.display = 'none';
        document.getElementById('errorMessage').style.display = 'none';
        const workflow = [];
        document.querySelectorAll('.milestone').forEach(milestone => {
            const milestoneData = {
                id: milestone.dataset.id,
                start: milestone.dataset.start,
                end: milestone.dataset.end,
                title: milestone.querySelector('h3').innerText,
                description: milestone.dataset.description,
                tasks: parseTasks(milestone.querySelector('.task-container')),
            };
            workflow.push(milestoneData);
        });

        //console.log('Workflow Structure:', );
        const formData = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            workflow_data: workflow,
            project_category_id: document.getElementById('project_category_id').value,
            company_id: document.getElementById('company_id').value,
        };

        try {
            const response = await axios.post('/api/v1/workflows', formData, {
                headers: {
                    'Authorization': `Bearer {{ auth()->user()->api_token }}`, // Replace with your token logic
                }
            });

            document.getElementById('successMessage').innerText = response.data.message || 'Workflow created successfully!';
            document.getElementById('successMessage').style.display = 'block';

            // Reset the form
            document.getElementById('workflowForm').reset();
        } catch (error) {
            document.getElementById('errorMessage').innerText = error.response?.data?.message || 'An error occurred.';
            document.getElementById('errorMessage').style.display = 'block';
        }
    }

     
  
// Parse tasks to include the updated template structure
function parseTasks(container) {
    const tasks = [];
    container.querySelectorAll(':scope > .task, :scope > .branch').forEach((item) => {
        let templates;
        try {
            templates = JSON.parse(item.dataset.templates || '[]');
        } catch (error) {
            console.error('Invalid JSON in task templates:', error);
            templates = []; // Default to an empty array if parsing fails
        }

        const taskData = {
            id: item.dataset.id,
            title: item.dataset.title,
            start: item.dataset.start,
            end: item.dataset.end,
            type: item.classList.contains('branch') ? 'branch' : item.dataset.type,
            description: item.dataset.description,
            templates: templates, // Include updated template structure
            subtasks: item.querySelector('.task-container')
                ? parseTasks(item.querySelector('.task-container'))
                : [],
        };
        tasks.push(taskData);
    });
    return tasks;
}


    function renderWorkflow(workflow) {
const container = document.getElementById('milestone-container');
container.innerHTML = ''; // Clear existing milestones

workflow.forEach((milestoneData) => {
    const milestone = document.createElement('div');
    milestone.classList.add('milestone');
    milestone.dataset.id = milestoneData.id;
    milestone.dataset.description = milestoneData.description || '';

    milestone.innerHTML = `
        <h3>
            <span class="title">${milestoneData.title}</span>
            <button type="button" class="btn btn-sm btn-link" onclick="openMilestoneModal(this.parentElement.parentElement)">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteMilestone(this.parentElement.parentElement)">
                <i class="bi bi-trash"></i>
            </button>
        </h3>
        <div class="task-container"></div>
    `;

    const taskContainer = milestone.querySelector('.task-container');

    milestoneData.tasks.forEach((taskData) => {
        renderTask(taskData, taskContainer);
    });

    container.appendChild(milestone);
});

initializeSortable(); // Reinitialize drag-and-drop
}

function renderTask(taskData, parentContainer) {
const task = document.createElement('div');
task.classList.add('task');
if (taskData.type === 'decision') task.classList.add('decision');
if (taskData.type === 'branch') task.classList.add('branch');

task.dataset.id = taskData.id;
task.dataset.type = taskData.type;
task.dataset.title = taskData.title || '';
task.dataset.description = taskData.description || '';
task.dataset.templates = JSON.stringify(taskData.templates);

if (taskData.type === 'decision') task.classList.add('decision');

task.innerHTML = `
    <span>${taskData.title}</span>
    
    <button type="button" class="btn btn-sm btn-link" onclick="openTaskModal(this.parentElement)">
        <i class="bi bi-pencil-square"></i>
    </button>
    <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteTask(this.parentElement)">
        <i class="bi bi-trash"></i>
    </button>

    ${taskData.type === 'decision' || taskData.type === 'branch' ? `<div class="task-container"></div>` : ''}
`;

parentContainer.appendChild(task);

// If the task has subtasks (for decisions and branches)
if (taskData.subtasks && taskData.subtasks.length > 0) {
    const subtaskContainer = task.querySelector('.task-container');
    taskData.subtasks.forEach((subtaskData) => {
        renderTask(subtaskData, subtaskContainer);
    });
}
}
    initializeSortable();
</script>
@endsection

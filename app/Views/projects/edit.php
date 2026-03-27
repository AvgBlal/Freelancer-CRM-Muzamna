<?php $title = __('projects.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("projects.edit") ?>: <?= htmlspecialchars($project['title']) ?></h2>
    </div>

    <form method="POST" action="/projects/<?= $project['id'] ?>">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.client') ?> *</label>
            <select name="client_id" class="form-select" required>
                <option value="">-- <?= __('tasks.select_client') ?> --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= $project['client_id'] == $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("projects.project_title") ?> *</label>
            <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($project['title']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.description") ?></label>
            <textarea name="description" class="form-textarea"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.status") ?></label>
            <select name="status" class="form-select">
                <option value="idea" <?= $project['status'] === 'idea' ? 'selected' : '' ?>><?= __("notes.cat.idea") ?></option>
                <option value="in_progress" <?= $project['status'] === 'in_progress' ? 'selected' : '' ?>><?= __("projects.status.in_progress") ?></option>
                <option value="paused" <?= $project['status'] === 'paused' ? 'selected' : '' ?>><?= __("projects.status.paused") ?></option>
                <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>><?= __("projects.status.completed") ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.status") ?></label>
            <select name="priority" class="form-select">
                <option value="low" <?= $project['priority'] === 'low' ? 'selected' : '' ?>><?= __("projects.priority.low") ?></option>
                <option value="normal" <?= $project['priority'] === 'normal' ? 'selected' : '' ?>><?= __("projects.priority.normal") ?></option>
                <option value="high" <?= $project['priority'] === 'high' ? 'selected' : '' ?>><?= __("projects.priority.high") ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("services.start_date") ?></label>
            <input type="date" name="start_date" class="form-input ltr-input" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("projects.delivery_date") ?></label>
            <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($project['due_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.progress') ?>: <span id="progress-val"><?= (int)$project['progress'] ?></span>%</label>
            <input type="range" name="progress" min="0" max="100" step="5"
                   value="<?= (int)$project['progress'] ?>"
                   class="form-input" style="padding:0; cursor:pointer;"
                   oninput="document.getElementById('progress-val').textContent=this.value; var bar=document.getElementById('progress-bar'); bar.style.width=this.value+'%'; bar.style.background=this.value==100?'#16a34a':'#2563eb';">
            <div style="background: #e5e7eb; border-radius: 4px; height: 8px; margin-top: 4px;">
                <div id="progress-bar" style="background: <?= $project['progress'] == 100 ? '#16a34a' : '#2563eb' ?>; width: <?= (int)$project['progress'] ?>%; height: 100%; border-radius: 4px; transition: width 0.2s;"></div>
            </div>
            <small class="form-hint"></small>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/projects/<?= $project['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
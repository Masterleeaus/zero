/// Local-state model for checklist execution flow.
/// Backed by app-layer state only until a backend sync endpoint is available.
/// TODO: wire to a persistent local store (Hive/shared_prefs) and backend PATCH
/// when /api/v1/provider/job-checklist/status is available.

enum ChecklistState {
  pending,
  active,
  paused,
  completed,
  verified,
}

extension ChecklistStateX on ChecklistState {
  String get label {
    switch (this) {
      case ChecklistState.pending:
        return 'checklist_state_pending';
      case ChecklistState.active:
        return 'checklist_state_active';
      case ChecklistState.paused:
        return 'checklist_state_paused';
      case ChecklistState.completed:
        return 'checklist_state_completed';
      case ChecklistState.verified:
        return 'checklist_state_verified';
    }
  }

  bool get canStart =>
      this == ChecklistState.pending || this == ChecklistState.paused;
  bool get canPause => this == ChecklistState.active;
  bool get canComplete => this == ChecklistState.active;
  bool get isFinished =>
      this == ChecklistState.completed || this == ChecklistState.verified;
}

class ChecklistExecutionModel {
  final String jobId;
  ChecklistState state;
  DateTime? startedAt;
  DateTime? completedAt;

  ChecklistExecutionModel({
    required this.jobId,
    this.state = ChecklistState.pending,
    this.startedAt,
    this.completedAt,
  });

  void start() {
    if (state.canStart) {
      state = ChecklistState.active;
      startedAt ??= DateTime.now();
    }
  }

  void pause() {
    if (state.canPause) {
      state = ChecklistState.paused;
    }
  }

  void complete() {
    if (state.canComplete) {
      state = ChecklistState.completed;
      completedAt = DateTime.now();
    }
  }
}

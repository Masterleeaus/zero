Identity model strip
====================

Company.php and Team.php were removed from the source pack because tenant and
identity ownership should stay with MagicAI. Any WorkCore relationships that
referenced these models should be rebound during merge to MagicAI tenant/user
models. Use company_id as the primary tenant key and preserve legacy team_id
only when compatibility requires it.

<?php

namespace App\Models;

/**
 * Backwards-compatible alias model: Team extends VikEquipe so older code
 * referencing Team keeps working while the canonical model is VikEquipe.
 */
class Team extends VikEquipe
{
    // thin alias for backwards compatibility
}

<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class TeamMemberViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        $name = $this->dataString('name');
        $photo = $this->dataMediaReference('photo');
        $position = $this->dataString('position');
        $bio = $this->dataString('bio');
        $linkedin = $this->dataString('linkedin_url');

        return [
            'name'     => $name,
            'photo'    => $photo,
            'position' => $position,
            'bio'      => $bio,
            'linkedin' => lang_url($linkedin, $this->lang),
        ];
    }
}

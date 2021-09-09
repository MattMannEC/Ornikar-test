<?php
namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\LessonRepository;
use App\Repository\MeetingPointRepository;

class TemplateManager
{
    public Learner $user;
    public array $contentVars;
    public array $subjectVars;

    public function __construct(array $data)
    {
        $this->setUser();
        $this->lesson = $data['lesson'] instanceof Lesson) ? $data['lesson'] : null;
        $this->contentVars = [
            'instructor_link' => $this->lesson->instructor->getLink() ?? '',
            'lesson:summary_html' => Lesson::renderHtml($this->lesson),
            'lesson:summary' => Lesson::renderText($this->lesson),
            'lesson:instructor_name' => $this->lesson->instructor->firstname,
            'lesson:meeting_point' => $this->lesson->name,
            'lesson:start_date' => $this->lesson->start_time->format('d/m/Y'),
            'lesson:start_time' => $this->lesson->start_time->format('H:i'),
            'lesson:end_time' => $this->lesson->end_time->format('H:i'),
            'user:first_name' => strtolower($this->user->firstname),
        ];

        $this->subjectVars = [
            'lesson:instructor_name' => $this->lesson->instructor->firstname,
        ];
    }

    public function setUser()
    {
        if ($data['user'] instanceof Learner) {
            $user = $data['user'];
        } else {
            $user = ApplicationContext::getInstance()->getCurrentUser();
        }
        $this->user = $user;
    }

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $tpl->subject = $this->setVariables($tpl->subject, $this->subjectVars);
        $tpl->content = $this->setVariables($tpl->content, $this->contentVars); 

        return $tpl;
    }

    private function setVariables($text, array $data)
    {

        foreach ($templateVars as $wildcard => $value) {
            str_replace($wildcard, $value, $text);
        }
            
        return $text;
    }
}

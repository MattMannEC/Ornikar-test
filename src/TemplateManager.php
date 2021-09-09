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

    public function __construct(array $data)
    {
        $this->user = $data['user'] instanceof Learner) ? $data['user'] : ApplicationContext::getInstance()->getCurrentUser();
        $this->lesson = $data['lesson'] instanceof Lesson) ? $data['lesson'] : null;
    }

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $tpl->subject = $this->computeText($tpl->subject, $data);
        $tpl->content = $this->computeText($tpl->content, $data);

        return $tpl;
    }

    private function computeText($text, array $data)
    {
        if ($this->lesson)
        {
            $text = str_replace('[instructor_link]',  'instructors/' . $this->lesson->instructor->id .'-'.urlencode($this->lesson->instructor->firstname), $text);

            $containsSummaryHtml = strpos($text, '[lesson:summary_html]');
            $containsSummary     = strpos($text, '[lesson:summary]');

            if ($containsSummaryHtml !== false) {
                $text = str_replace(
                    '[lesson:summary_html]',
                    Lesson::renderHtml($this->lesson),
                    $text
                );
            }

            if ($containsSummary !== false) {
                $text = str_replace(
                    '[lesson:summary]',
                    Lesson::renderText($this->lesson),
                    $text
                );
            }

        $text = str_replace('[lesson:instructor_name]',$this->lesson->instructor->firstname, $text);
        }

        if ($this->lesson->meetingPointId) {
                $text = str_replace('[lesson:meeting_point]', $this->lesson->name, $text);
        }

        $text = str_replace('[lesson:start_date]', $this->lesson->start_time->format('d/m/Y'), $text);
        $text = str_replace('[lesson:start_time]', $this->lesson->start_time->format('H:i'), $text);
        $text = str_replace('[lesson:end_time]', $this->lesson->end_time->format('H:i'), $text);

            if ($data['instructor']  instanceof Instructor)
                $text = str_replace('[instructor_link]',  'instructors/' . $data['instructor']->id .'-'.urlencode($data['instructor']->firstname), $text);
            else
                $text = str_replace('[instructor_link]', '', $text);

        $text = str_replace('[user:first_name]', strtolower($this->user->firstname), $text);

        return $text;
    }

    public function getInstructorLink()
    {
        return 'instructors/' . $this->lesson->instructor->id .'-'.urlencode($this->lesson->instructor->firstname);
    }
}

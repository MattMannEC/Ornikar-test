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
        $lesson = ($data['lesson'] instanceof Lesson) ? $data['lesson'] : null;

        if ($lesson)
        {
            $_lessonFromRepository = LessonRepository::getInstance()->getById($lesson->id);
            $instructorOfLesson = InstructorRepository::getInstance()->getById($lesson->instructorId);

            $text = str_replace('[instructor_link]',  'instructors/' . $instructorOfLesson->id .'-'.urlencode($instructorOfLesson->firstname), $text);

            $containsSummaryHtml = strpos($text, '[lesson:summary_html]');
            $containsSummary     = strpos($text, '[lesson:summary]');

            if ($containsSummaryHtml !== false) {
                $text = str_replace(
                    '[lesson:summary_html]',
                    Lesson::renderHtml($_lessonFromRepository),
                    $text
                );
            }

            if ($containsSummary !== false) {
                $text = str_replace(
                    '[lesson:summary]',
                    Lesson::renderText($_lessonFromRepository),
                    $text
                );
            }

        $text = str_replace('[lesson:instructor_name]',$instructorOfLesson->firstname,$text);
        }

        if ($lesson->meetingPointId) {
                $text = str_replace('[lesson:meeting_point]', $lesson->name, $text);
        }

        $text = str_replace('[lesson:start_date]', $lesson->start_time->format('d/m/Y'), $text);
        $text = str_replace('[lesson:start_time]', $lesson->start_time->format('H:i'), $text);
        $text = str_replace('[lesson:end_time]', $lesson->end_time->format('H:i'), $text);

            if ($data['instructor']  instanceof Instructor)
                $text = str_replace('[instructor_link]',  'instructors/' . $data['instructor']->id .'-'.urlencode($data['instructor']->firstname), $text);
            else
                $text = str_replace('[instructor_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $_user  = ($data['user']  instanceof Learner)  ? $data['user']  : ApplicationContext::getInstance()->getCurrentUser();
        if($_user) {
            $text = str_replace('[user:first_name]', strtolower($_user->firstname), $text);
        }

        return $text;
    }

    public function replaceString()
    {
        
    }
}



// method replace text

// method getUser construct ?




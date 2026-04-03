<?php

namespace Modules\Recruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\AIAssistant\Entities\AssistantTemplate;

class AIAssistantTemplateListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $defaultTemplate = [
            [
                'template_name'=>'title',
                'template_module'=>'job',
                'prompt'=> "Generate a list of job titles commonly found in an ##work_place##. The job titles should cover a range of roles and responsibilities within the field of ##field##. Include positions such as ##positions##. Please provide a diverse selection of job titles that accurately reflect the various positions found in an ##work_place##.",
                'field_json'=>'{"field":[{"label":"Work Place","placeholder":"e.g.IT Company,hospital","field_type":"text_box","field_name":"work_place"},{"label":"Field ","placeholder":"e.g.Backend","field_type":"text_box","field_name":"field"},{"label":"Positions","placeholder":"e.g.developer,tester","field_type":"text_box","field_name":"positions"}]}',
                'is_tone'=> 0,
            ],
            [
                'template_name'=>'description',
                'template_module'=>'job',
                'prompt'=> "Generate a job descriptions for a ##position## position. The job description should include responsibilities such as ##responsibilities##. Please ensure the descriptions are concise, informative, and accurately reflect the key responsibilities of a ##position##.",
                'field_json'=>'{"field":[{"label":"Position","placeholder":"","field_type":"text_box","field_name":"position"},{"label":"Responsibilities","placeholder":"","field_type":"textarea","field_name":"responsibilities"}]}',
                'is_tone'=> 0,
            ],
            [
                'template_name'=>'requirement',
                'template_module'=>'job',
                'prompt'=> "Generate a comma-separated string of job requirements for a ##position## position. The requirements should include ##description##. Please provide the requirements in a comma-separated string format.",
                'field_json'=>'{"field":[{"label":"Position","placeholder":"","field_type":"text_box","field_name":"position"},{"label":"Description","placeholder":"","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=> 0,
            ],
            [
                'template_name' => 'comment',
                'template_module' => 'interview-schedule',
                'prompt' => "Generate an announcement title for ##comment##. The title should be attention-grabbing and informative, effectively conveying the key message to the intended audience. Please ensure the title is appropriate for the given situation, whether it's about a ##comment##. Aim to create a title that captures the essence of the announcement and sparks interest or curiosity among the readers.",
                'field_json' => '{"field":[{"label":"Interview Schedule Comment","placeholder":"e.g.Growth Opportunities","field_type":"textarea","field_name":"comment"}]}',
                'is_tone' => '1',
            ],
            [
                'template_name'=>'offer_content',
                'template_module'=>'offer letter settings',
                'prompt'=> "Generate a joining offer letter for {applicant_name} who has been selected for the position of {job_title} at {app_name}. Customize the letter by filling in the appropriate details and placeholders enclosed in {}. Please structure the letter to include the mentioned variables in their respective sections. Sign off the letter with the company name.Variables:{applicant_name},{app_name},{job_title},{start_date},{workplace_location},{days_of_week},{salary},{salary_type},{offer_expiration_date} and  letter must be descriptive with eficient information",
                'field_json'=>'{"field":[{"label":"Company  Policy/Condition (comma seperated string)","placeholder":"e.g.leave,holiday,salary","field_type":"textarea","field_name":"conditions"}]}',
                'is_tone'=> 0,
            ],
        ];

        foreach($defaultTemplate as $temp)
        {
            $check = AssistantTemplate::where('template_module',$temp['template_module'])->where('module','Recruitment')->where('template_name',$temp['template_name'])->exists();
            if(!$check)
            {
                AssistantTemplate::create(
                    [
                        'template_name' => $temp['template_name'],
                        'template_module' => $temp['template_module'],
                        'module' => 'Recruitment',
                        'prompt' => $temp['prompt'],
                        'field_json' => $temp['field_json'],
                        'is_tone' => $temp['is_tone'],
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
        }
    }
}

# Zylos Bridge Template

class ZylosBridge
{
    public function callSkill($skill, $payload)
    {
        return Http::post(env('ZYLOS_URL')."/skills/".$skill, $payload);
    }
}

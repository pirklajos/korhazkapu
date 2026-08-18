<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ContentTemplate;
use App\Entity\Feedback;
use App\Entity\InformationPage;
use App\Entity\Institution;
use App\Entity\SearchSynonym;
use App\Entity\TemplateAdoption;
use PHPUnit\Framework\TestCase;

final class SearchFeedbackTemplateTest extends TestCase
{
    public function testSynonymsAreTrimmedAndDeduplicated(): void
    {
        $institution = $this->institution('elso');
        $synonym = new SearchSynonym($institution, 'kardiológia', [' szív ', 'szív', '', 'kardio']);

        self::assertSame(['szív', 'kardio'], $synonym->getSynonyms());
    }

    public function testFeedbackKeepsPageContextAndOptionalComment(): void
    {
        $feedback = new Feedback($this->institution('elso'), '/i/elso/ellatasok/ekg', true, ' Hasznos volt. ');

        self::assertTrue($feedback->isHelpful());
        self::assertSame('/i/elso/ellatasok/ekg', $feedback->getPagePath());
        self::assertSame('Hasznos volt.', $feedback->getComment());
    }

    public function testTemplateCannotBeAdoptedIntoAnotherTenantPage(): void
    {
        $first = $this->institution('elso');
        $second = $this->institution('masodik');
        $template = new ContentTemplate('erkezes', 'Érkezés', null, ['blocks' => []]);
        $page = new InformationPage($second, 'Érkezés', 'erkezes');

        $this->expectException(\DomainException::class);
        new TemplateAdoption($first, $template, $page);
    }

    private function institution(string $slug): Institution
    {
        return new Institution(ucfirst($slug), strtoupper($slug), $slug, 'Budapest');
    }
}

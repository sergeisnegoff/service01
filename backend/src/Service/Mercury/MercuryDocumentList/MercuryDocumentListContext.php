<?php
declare(strict_types=1);

namespace App\Service\Mercury\MercuryDocumentList;

use App\Model\Company;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class MercuryDocumentListContext
{
    protected ?Company $company = null;
    protected string $query = '';
    protected string $sort = 'DESC';
    protected string $sortBy = 'issue_date';
    protected string $sender = '';
    protected string $status = '';
    protected ?DateTime $issueDate = null;

    /**
     * @return DateTime|null
     */
    public function getIssueDate(): ?DateTime
    {
        return $this->issueDate;
    }

    /**
     * @param DateTime|null $issueDate
     */
    public function setIssueDate(?DateTime $issueDate): self
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    /**
     * @param string $sortBy
     */
    public function setSortBy(string $sortBy): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company|null $company
     */
    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    public function fillFromRequest(Request $request)
    {
        $this->setQuery($request->query->get('query', ''));

        if ($request->query->get('sort')) {
            $this->setSort($request->query->get('sort'));
        }

        if ($request->query->get('sortBy')) {
            $this->setSortBy($request->query->get('sortBy'));
        }

        if ($request->query->get('sender')) {
            $this->setSender($request->query->get('sender'));
        }

        if ($request->query->get('status')) {
            $this->setStatus($request->query->get('status'));
        }

        $issueDate = $request->query->get('issueDate');

        if ($issueDate && strtotime($issueDate)) {
            $this->setIssueDate(new DateTime($issueDate));
        }
    }
}

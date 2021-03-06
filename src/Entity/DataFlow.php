<?php

/*
 * This file is part of itk-dev/datatidy.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Traits\BlameableEntity;
use Cron\CronExpression;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataFlowRepository")
 * @Gedmo\Loggable()
 */
class DataFlow
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned()
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataSource", inversedBy="dataFlows")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataSource;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DataTransform", mappedBy="dataFlow", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $transforms;

    /**
     * @ORM\Column(type="boolean")
     * @Gedmo\Versioned()
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastRunAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DataTarget", mappedBy="dataFlow", cascade={"persist"}, orphanRemoval=true)
     */
    private $dataTargets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DataFlowJob", mappedBy="dataFlow", orphanRemoval=true)
     */
    private $jobs;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     * @ORM\JoinTable(name="data_flow_collaborator")
     */
    private $collaborators;

    /**
     * @ORM\Column(type="cron_expression")
     * @Gedmo\Versioned()
     */
    private $schedule;

    public function __construct()
    {
        $this->transforms = new ArrayCollection();
        $this->enabled = false;
        $this->dataTargets = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->collaborators = new ArrayCollection();
        $this->schedule = CronExpression::factory('@monthly');
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return DataFlow
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDataSource(): ?DataSource
    {
        return $this->dataSource;
    }

    public function setDataSource(?DataSource $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * @return Collection|DataTransform[]
     */
    public function getTransforms(): Collection
    {
        return $this->transforms;
    }

    public function addTransform(DataTransform $transform): self
    {
        if (!$this->transforms->contains($transform)) {
            $this->transforms[] = $transform;
            $transform->setDataFlow($this);
        }

        return $this;
    }

    public function removeTransform(DataTransform $transform): self
    {
        if ($this->transforms->contains($transform)) {
            $this->transforms->removeElement($transform);
            // set the owning side to null (unless already changed)
            if ($transform->getDataFlow() === $this) {
                $transform->setDataFlow(null);
            }
        }

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLastRunAt(): ?\DateTimeInterface
    {
        return $this->lastRunAt;
    }

    public function setLastRunAt(?\DateTimeInterface $lastRunAt): self
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s (#%s)', $this->name ?? static::class, $this->id);
    }

    /**
     * @return Collection|DataTarget[]
     */
    public function getDataTargets(): Collection
    {
        return $this->dataTargets;
    }

    public function addDataTarget(DataTarget $dataTarget): self
    {
        if (!$this->dataTargets->contains($dataTarget)) {
            $this->dataTargets[] = $dataTarget;
            $dataTarget->setDataFlow($this);
        }

        return $this;
    }

    public function removeDataTarget(DataTarget $dataTarget): self
    {
        if ($this->dataTargets->contains($dataTarget)) {
            $this->dataTargets->removeElement($dataTarget);
            // set the owning side to null (unless already changed)
            if ($dataTarget->getDataFlow() === $this) {
                $dataTarget->setDataFlow(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DataFlowJob[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(DataFlowJob $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setDataFlow($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getCollaborators(): Collection
    {
        return $this->collaborators;
    }

    public function addCollaborator(User $collaborator): self
    {
        if (!$this->collaborators->contains($collaborator)) {
            $this->collaborators[] = $collaborator;
        }

        return $this;
    }

    public function removeJob(DataFlowJob $job): self
    {
        if ($this->jobs->contains($job)) {
            $this->jobs->removeElement($job);
            // set the owning side to null (unless already changed)
            if ($job->getDataFlow() === $this) {
                $job->setDataFlow(null);
            }
        }

        return $this;
    }

    public function removeCollaborator(User $collaborator): self
    {
        if ($this->collaborators->contains($collaborator)) {
            $this->collaborators->removeElement($collaborator);
        }

        return $this;
    }

    public function getSchedule(): CronExpression
    {
        return $this->schedule;
    }

    public function setSchedule($schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }
}

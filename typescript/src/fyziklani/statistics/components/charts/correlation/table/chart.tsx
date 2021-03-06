import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '../../../../../../i18n/i18n';
import {
    Submit,
    Submits,
    Task,
    Team,
} from '../../../../../helpers/interfaces';
import {
    getTimeLabel,
    PreprocessedSubmit,
} from '../../../../middleware/charts/correlation';
import { getAverageNStandardDeviation } from '../../../../middleware/charts/std-dev';
import { Store as StatisticsStore } from '../../../../reducers';

interface State {
    submits?: Submits;
    tasks?: Task[];
    teams?: Team[];
    firstTeamId?: number;
    secondTeamId?: number;
}

class Table extends React.Component<State, {}> {

    public render() {

        const {firstTeamId, secondTeamId, submits, tasks} = this.props;
        const firstTeamSubmits: Submit[] = [];
        const secondTeamSubmits: Submit[] = [];
        for (const id in submits) {
            if (submits.hasOwnProperty(id)) {
                const submit = submits[id];
                if (submit.teamId === firstTeamId) {
                    firstTeamSubmits.push(submit);
                } else if (submit.teamId === secondTeamId) {
                    secondTeamSubmits.push(submit);
                }
            }
        }
        const submitsForTeams: { [teamId: number]: { [taskId: number]: PreprocessedSubmit } } = {};
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                const {teamId, taskId: taskId} = submit;
                submitsForTeams[teamId] = submitsForTeams[teamId] || {};
                submitsForTeams[teamId][taskId] = {
                    ...submit,
                    timestamp: (new Date(submit.created)).getTime(),
                };
            }
        }

        const rows = [];
        const deltas = [];
        let count = 0;
        const firstTeamData = submitsForTeams.hasOwnProperty(firstTeamId) ? submitsForTeams[firstTeamId] : {};
        const secondTeamData = submitsForTeams.hasOwnProperty(secondTeamId) ? submitsForTeams[secondTeamId] : {};
        tasks.forEach((task: Task, id) => {
            const firstSubmit = firstTeamData.hasOwnProperty(task.taskId) ? firstTeamData[task.taskId] : null;
            const secondSubmit = secondTeamData.hasOwnProperty(task.taskId) ? secondTeamData[task.taskId] : null;
            let delta = 0;
            if (firstSubmit && secondSubmit) {
                count++;
                delta = Math.abs(firstSubmit.timestamp - secondSubmit.timestamp);
                deltas.push(delta);
            }
            rows.push(<tr key={id}>
                <td>{task.label}</td>
                <td>{firstSubmit ? firstSubmit.created : ''}</td>
                <td>{secondSubmit ? secondSubmit.created : ''}</td>
                <td>{delta ? (getTimeLabel(delta, 0)) : ''}</td>
            </tr>);

        });
        const avgNStdDev = getAverageNStandardDeviation(deltas);
        return <div>
            <table className={'table table-striped table-hover table-sm'}>
                <thead>
                <tr>
                    <th>{lang.getText('Task')}</th>
                    <th>{lang.getText('Time first team')}</th>
                    <th>{lang.getText('Time second team')}</th>
                    <th>{lang.getText('Difference')}</th>
                </tr>

                </thead>
                <tbody>{rows}</tbody>
            </table>
            <p>
                <span>{firstTeamSubmits.length} {lang.getText('first team')}</span>
                <span>{secondTeamSubmits.length} {lang.getText('second team')}</span>
                <span>{count} {lang.getText('both teams')}</span>
                <span>{getTimeLabel(avgNStdDev.average, avgNStdDev.standardDeviation)} {lang.getText('per task')}</span>
            </p>
        </div>;

    }
}

const mapStateToProps = (state: StatisticsStore): State => {
    return {
        firstTeamId: state.statistics.firstTeamId,
        secondTeamId: state.statistics.secondTeamId,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Table);

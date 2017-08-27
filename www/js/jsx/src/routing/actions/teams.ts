import { ITeam } from '../interfaces';

export const ACTION_ADD_TEAMS = 'ACTION_ADD_TEAMS';

export const addTeams = (teams: ITeam[]) => {
    return {
        teams,
        type: ACTION_ADD_TEAMS,
    };
};

export const ACTION_REMOVE_PLACE = 'ACTION_REMOVE_PLACE';

export const removeTeamPlace = (teamID: number) => {
    return {
        teamID,
        type: ACTION_REMOVE_PLACE,
    };
};
